<?php

declare(strict_types=1);

namespace App\Services\Schema;

use App\Services\Schema\Contracts\SchemaPushDriver;
use Throwable;

/**
 * Orchestrates a schema push: compile the schema to DDL, normalize the
 * connection, then dispatch to a driver. The connection-string (direct PDO)
 * path is wired now; a Supabase Management API driver can be added behind the
 * same SchemaPushDriver contract without touching this class.
 */
final class PostgresSchemaPusher
{
    public function __construct(
        private readonly PostgresSchemaCompiler $compiler,
        private readonly PostgresConnectionFactory $factory,
        private readonly SchemaPushDriver $driver,
    ) {}

    /**
     * @param  array{name?: string, tables?: array<int, array<string, mixed>>}  $schema
     * @param  array<string, mixed>  $connection
     * @param  array<string, mixed>  $options
     * @return array{ok: bool, results: list<array{sql: string, ok: bool, error: string|null}>, message: string}
     */
    public function push(array $schema, array $connection, array $options = []): array
    {
        $statements = $this->compiler->compile($schema);

        if ($statements === []) {
            return ['ok' => false, 'results' => [], 'message' => 'Nothing to push — add a table first.'];
        }

        try {
            $config = $this->factory->build($connection);
        } catch (Throwable $e) {
            return ['ok' => false, 'results' => [], 'message' => $e->getMessage()];
        }

        return $this->driver->push($statements, $config);
    }
}
