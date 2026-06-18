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
 * circular / self-referential relationships never fail. A foreign-key column is
 * emitted with the type of the column it references, and relationships whose
 * target is not a primary key / unique column (which Postgres cannot reference)
 * are skipped — see unsupportedRelationships(). Pure: no DB access.
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
        $meta = $this->buildMeta($tables);

        $create = [];
        foreach ($tables as $table) {
            $create[] = $this->createTable($table, $meta);
        }

        $foreignKeys = [];
        foreach ($tables as $table) {
            array_push($foreignKeys, ...$this->foreignKeys($table, $meta));
        }

        $indexes = [];
        foreach ($tables as $table) {
            array_push($indexes, ...$this->indexes($table));
        }

        return [...$create, ...$foreignKeys, ...$indexes];
    }

    /**
     * Human-readable warnings for relationships that cannot be created in
     * Postgres (target table/column missing, or the target column is not a
     * primary key / unique). These are skipped by compile() rather than failing.
     *
     * @param  array{name?: string, tables?: array<int, array<string, mixed>>}  $schema
     * @return list<string>
     */
    public function unsupportedRelationships(array $schema): array
    {
        $tables = array_values($schema['tables'] ?? []);
        $meta = $this->buildMeta($tables);

        $warnings = [];
        foreach ($tables as $table) {
            foreach (($table['columns'] ?? []) as $column) {
                $fk = $column['fk'] ?? null;
                if (! is_array($fk) || empty($fk['table'])) {
                    continue;
                }
                if ($this->resolveFk($fk, $meta) !== null) {
                    continue; // valid — will be created
                }

                $targetTable = $meta[(string) $fk['table']]['name'] ?? '(unknown table)';
                $targetColumn = (string) ($fk['column'] ?? 'id');
                $warnings[] = "{$table['name']}.{$column['name']} → {$targetTable}.{$targetColumn} "
                    .'(target column must be a primary key or unique)';
            }
        }

        return $warnings;
    }

    /**
     * Index each table by its client_id, capturing the real name and, per column,
     * its type and whether it is a valid FK target (primary key or unique).
     *
     * @param  list<array<string, mixed>>  $tables
     * @return array<string, array{name: string, columns: array<string, array{type: string, key: bool}>}>
     */
    private function buildMeta(array $tables): array
    {
        $meta = [];
        foreach ($tables as $table) {
            $name = (string) ($table['name'] ?? '');
            PostgresIdentifier::assertValid($name, 'table name');

            $columns = [];
            foreach (($table['columns'] ?? []) as $column) {
                $columns[(string) ($column['name'] ?? '')] = [
                    'type' => (string) ($column['type'] ?? ''),
                    'key' => ! empty($column['pk']) || ! empty($column['unique']),
                ];
            }

            $meta[(string) ($table['id'] ?? '')] = ['name' => $name, 'columns' => $columns];
        }

        return $meta;
    }

    /**
     * @param  array<string, mixed>  $table
     * @param  array<string, array{name: string, columns: array<string, array{type: string, key: bool}>}>  $meta
     */
    private function createTable(array $table, array $meta): string
    {
        $name = (string) $table['name'];
        $lines = [];
        $primary = [];

        foreach (($table['columns'] ?? []) as $column) {
            $colName = (string) ($column['name'] ?? '');
            PostgresIdentifier::assertValid($colName, 'column name');

            $type = $this->columnType($column, $meta);
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
     * declared type when the FK target cannot be resolved.
     *
     * @param  array<string, mixed>  $column
     * @param  array<string, array{name: string, columns: array<string, array{type: string, key: bool}>}>  $meta
     */
    private function columnType(array $column, array $meta): string
    {
        $target = $this->resolveFk($column['fk'] ?? null, $meta);
        if ($target !== null) {
            return $this->referenceType($target['type']);
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
     * Resolve a column's FK to a creatable target, or null when it cannot be
     * created: target table/column missing, or the target column is not a
     * primary key / unique (Postgres requires the referenced column to be unique).
     *
     * @param  array<string, array{name: string, columns: array<string, array{type: string, key: bool}>}>  $meta
     * @return array{table: string, column: string, type: string}|null
     */
    private function resolveFk(mixed $fk, array $meta): ?array
    {
        if (! is_array($fk) || empty($fk['table'])) {
            return null;
        }

        $target = $meta[(string) $fk['table']] ?? null;
        if ($target === null) {
            return null;
        }

        $refColumn = (string) ($fk['column'] ?? 'id');
        $refMeta = $target['columns'][$refColumn] ?? null;
        if ($refMeta === null || $refMeta['key'] !== true) {
            return null;
        }

        return ['table' => $target['name'], 'column' => $refColumn, 'type' => $refMeta['type']];
    }

    /**
     * @param  array<string, mixed>  $table
     * @param  array<string, array{name: string, columns: array<string, array{type: string, key: bool}>}>  $meta
     * @return list<string>
     */
    private function foreignKeys(array $table, array $meta): array
    {
        $tableName = (string) $table['name'];
        $out = [];

        foreach (($table['columns'] ?? []) as $column) {
            $target = $this->resolveFk($column['fk'] ?? null, $meta);
            if ($target === null) {
                continue; // missing or non-unique target — reported via unsupportedRelationships()
            }

            $colName = (string) $column['name'];
            PostgresIdentifier::assertValid($colName, 'column name');
            PostgresIdentifier::assertValid($target['column'], 'referenced column');

            $constraint = $tableName.'_'.$colName.'_fkey';
            PostgresIdentifier::assertValid($constraint, 'constraint name');

            $fk = $column['fk'];
            $alter = 'ALTER TABLE '.PostgresIdentifier::quote($tableName)
                .' ADD CONSTRAINT '.PostgresIdentifier::quote($constraint)
                .' FOREIGN KEY ('.PostgresIdentifier::quote($colName).')'
                .' REFERENCES '.PostgresIdentifier::quote($target['table'])
                .' ('.PostgresIdentifier::quote($target['column']).')'
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
