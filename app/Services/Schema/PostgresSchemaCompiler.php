<?php

declare(strict_types=1);

namespace App\Services\Schema;

/**
 * Compiles the builder's schema array into an ordered list of create-only
 * PostgreSQL DDL statements:
 *
 *   1. CREATE TABLE IF NOT EXISTS  — columns, PK, UNIQUE, NOT NULL, DEFAULT
 *   2. ALTER TABLE ... ADD CONSTRAINT  — foreign keys, each guarded by a
 *      pg_constraint existence check so a re-push is a no-op rather than an error
 *   3. CREATE INDEX IF NOT EXISTS
 *
 * All tables are created before any foreign key is added, so table ordering and
 * circular / self-referential relationships never fail. Pure: no DB access, so
 * it is unit-testable as plain string output.
 */
final class PostgresSchemaCompiler
{
    /** Mirror of PG_TYPE in resources/js/schematic.js. */
    private const TYPE = [
        'id' => 'BIGSERIAL',
        'bigInteger' => 'BIGINT',
        'unsignedBigInteger' => 'BIGINT',
        'integer' => 'INTEGER',
        'string' => 'VARCHAR(255)',
        'text' => 'TEXT',
        'boolean' => 'BOOLEAN',
        'date' => 'DATE',
        'datetime' => 'TIMESTAMP',
        'timestamp' => 'TIMESTAMP',
        'json' => 'JSONB',
        'decimal' => 'DECIMAL(8,2)',
        'float' => 'DOUBLE PRECISION',
        'uuid' => 'UUID',
    ];

    private const FK_ACTIONS = ['cascade', 'restrict', 'set null', 'no action'];

    /**
     * @param  array{name?: string, tables?: array<int, array<string, mixed>>}  $schema
     * @return list<string>
     */
    public function compile(array $schema): array
    {
        $tables = array_values($schema['tables'] ?? []);

        // Pass 0 — fk.table holds the target table's client_id. Map client_id -> real
        // name, and record each table's column types so a FK column can be emitted with
        // the SAME type as the column it references (Postgres rejects mismatched FKs).
        $nameByClientId = [];
        $typesByClientId = [];
        foreach ($tables as $table) {
            $name = (string) ($table['name'] ?? '');
            PostgresIdentifier::assertValid($name, 'table name');
            $clientId = (string) ($table['id'] ?? '');
            $nameByClientId[$clientId] = $name;

            $columnTypes = [];
            foreach (($table['columns'] ?? []) as $column) {
                $columnTypes[(string) ($column['name'] ?? '')] = (string) ($column['type'] ?? '');
            }
            $typesByClientId[$clientId] = $columnTypes;
        }

        $create = [];
        foreach ($tables as $table) {
            $create[] = $this->createTable($table, $typesByClientId);
        }

        $foreignKeys = [];
        foreach ($tables as $table) {
            array_push($foreignKeys, ...$this->foreignKeys($table, $nameByClientId));
        }

        $indexes = [];
        foreach ($tables as $table) {
            array_push($indexes, ...$this->indexes($table));
        }

        return [...$create, ...$foreignKeys, ...$indexes];
    }

    /**
     * @param  array<string, mixed>  $table
     * @param  array<string, array<string, string>>  $typesByClientId  client_id => [column name => type]
     */
    private function createTable(array $table, array $typesByClientId): string
    {
        $name = (string) $table['name'];
        $lines = [];
        $primary = [];

        foreach (($table['columns'] ?? []) as $column) {
            $colName = (string) ($column['name'] ?? '');
            PostgresIdentifier::assertValid($colName, 'column name');

            $type = $this->columnType($column, $typesByClientId);
            $line = '    '.PostgresIdentifier::quote($colName).' '.$type;
            $line .= empty($column['nullable']) ? ' NOT NULL' : ' NULL';

            $default = (string) ($column['default'] ?? '');
            if ($default !== '') {
                $line .= ' DEFAULT '.PostgresIdentifier::defaultLiteral($default);
            }

            if (! empty($column['unique'])) {
                $line .= ' UNIQUE';
            }

            $lines[] = $line;

            if (! empty($column['pk'])) {
                $primary[] = PostgresIdentifier::quote($colName);
            }
        }

        if ($primary !== []) {
            $lines[] = '    PRIMARY KEY ('.implode(', ', $primary).')';
        }

        return 'CREATE TABLE IF NOT EXISTS '.PostgresIdentifier::quote($name)
            ." (\n".implode(",\n", $lines)."\n)";
    }

    /**
     * The SQL type for a column. A foreign-key column is emitted with the type of
     * the column it references (an `id`/serial PK is referenced as plain BIGINT),
     * so the constraint's key types always match. Falls back to the column's own
     * declared type when the target cannot be resolved.
     *
     * @param  array<string, mixed>  $column
     * @param  array<string, array<string, string>>  $typesByClientId
     */
    private function columnType(array $column, array $typesByClientId): string
    {
        $fk = $column['fk'] ?? null;
        if (is_array($fk) && ! empty($fk['table'])) {
            $refType = $typesByClientId[(string) $fk['table']][(string) ($fk['column'] ?? 'id')] ?? null;
            if ($refType !== null) {
                return $this->referenceType($refType);
            }
        }

        return self::TYPE[$column['type'] ?? ''] ?? 'VARCHAR(255)';
    }

    /** The referencing type for a target column: a serial (`id`) PK is referenced as BIGINT. */
    private function referenceType(string $declaredType): string
    {
        if ($declaredType === 'id') {
            return 'BIGINT';
        }

        return self::TYPE[$declaredType] ?? 'VARCHAR(255)';
    }

    /**
     * @param  array<string, mixed>  $table
     * @param  array<string, string>  $nameByClientId
     * @return list<string>
     */
    private function foreignKeys(array $table, array $nameByClientId): array
    {
        $tableName = (string) $table['name'];
        $out = [];

        foreach (($table['columns'] ?? []) as $column) {
            $fk = $column['fk'] ?? null;
            if (! is_array($fk) || empty($fk['table'])) {
                continue;
            }

            $target = $nameByClientId[(string) $fk['table']] ?? null;
            if ($target === null) {
                continue; // relationship points at a table not present in this payload
            }

            $colName = (string) $column['name'];
            $refColumn = (string) ($fk['column'] ?? 'id');
            PostgresIdentifier::assertValid($colName, 'column name');
            PostgresIdentifier::assertValid($refColumn, 'referenced column');

            $constraint = $tableName.'_'.$colName.'_fkey';
            PostgresIdentifier::assertValid($constraint, 'constraint name');

            $alter = 'ALTER TABLE '.PostgresIdentifier::quote($tableName)
                .' ADD CONSTRAINT '.PostgresIdentifier::quote($constraint)
                .' FOREIGN KEY ('.PostgresIdentifier::quote($colName).')'
                .' REFERENCES '.PostgresIdentifier::quote($target)
                .' ('.PostgresIdentifier::quote($refColumn).')'
                .$this->referentialAction('ON DELETE', $fk['onDelete'] ?? null)
                .$this->referentialAction('ON UPDATE', $fk['onUpdate'] ?? null);

            // Re-push safety: ADD CONSTRAINT has no IF NOT EXISTS, so guard it.
            $literal = "'".str_replace("'", "''", $constraint)."'";
            $out[] = "DO \$\$\nBEGIN\n"
                ."    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = {$literal}) THEN\n"
                ."        {$alter};\n"
                ."    END IF;\nEND\n\$\$";
        }

        return $out;
    }

    private function referentialAction(string $clause, ?string $action): string
    {
        $action = strtolower(trim((string) $action));

        if ($action === '' || $action === 'no action' || ! in_array($action, self::FK_ACTIONS, true)) {
            return '';
        }

        return ' '.$clause.' '.strtoupper($action);
    }

    /**
     * @param  array<string, mixed>  $table
     * @return list<string>
     */
    private function indexes(array $table): array
    {
        $tableName = (string) $table['name'];
        $out = [];

        foreach (($table['columns'] ?? []) as $column) {
            if (empty($column['index'])) {
                continue;
            }

            $colName = (string) $column['name'];
            PostgresIdentifier::assertValid($colName, 'column name');

            $index = $tableName.'_'.$colName.'_index';
            PostgresIdentifier::assertValid($index, 'index name');

            $out[] = 'CREATE INDEX IF NOT EXISTS '.PostgresIdentifier::quote($index)
                .' ON '.PostgresIdentifier::quote($tableName)
                .' ('.PostgresIdentifier::quote($colName).')';
        }

        return $out;
    }
}
