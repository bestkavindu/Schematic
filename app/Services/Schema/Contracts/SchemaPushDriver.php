<?php

declare(strict_types=1);

namespace App\Services\Schema\Contracts;

/**
 * Executes a list of compiled DDL statements against a target database.
 *
 * Implementations: DirectPdoPushDriver (direct PDO connection — wired now);
 * a Supabase Management API driver can be added behind this same contract.
 */
interface SchemaPushDriver
{
    /**
     * @param  list<string>  $statements
     * @param  array<string, mixed>  $config  normalized connection config
     * @return array{ok: bool, results: list<array{sql: string, ok: bool, error: string|null}>, message: string}
     */
    public function push(array $statements, array $config): array;
}
