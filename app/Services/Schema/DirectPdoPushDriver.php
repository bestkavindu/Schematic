<?php

declare(strict_types=1);

namespace App\Services\Schema;

use App\Services\Schema\Contracts\SchemaPushDriver;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Executes DDL against a user-supplied PostgreSQL database over a direct PDO
 * connection registered at runtime. All statements run inside one transaction
 * (Postgres has transactional DDL), so any failure rolls the whole push back
 * and leaves the target database untouched.
 */
final class DirectPdoPushDriver implements SchemaPushDriver
{
    public function push(array $statements, array $config): array
    {
        $name = PostgresConnectionFactory::NAME;
        $password = (string) ($config['password'] ?? '');

        config(["database.connections.{$name}" => $config]);
        DB::purge($name);

        $results = [];

        try {
            $connection = DB::connection($name);
            $pdo = $connection->getPdo(); // force the handshake so auth / SSL / timeout errors surface here

            $connection->transaction(function () use ($pdo, $statements, &$results): void {
                foreach ($statements as $sql) {
                    // Raw exec on the transaction's PDO: DDL is dynamic (not a literal
                    // string) and needs no placeholder parsing for the DO $$ blocks.
                    $pdo->exec($sql);
                    $results[] = ['sql' => $sql, 'ok' => true, 'error' => null];
                }
            });

            return ['ok' => true, 'results' => $results, 'message' => 'Schema pushed successfully.'];
        } catch (Throwable $e) {
            $message = $this->scrub($e->getMessage(), $password);
            $results[] = [
                'sql' => $statements[count($results)] ?? '(connection)',
                'ok' => false,
                'error' => $message,
            ];

            return ['ok' => false, 'results' => $results, 'message' => "Push failed: {$message}"];
        } finally {
            DB::purge($name); // never leave the connection (or its credentials) resolved
        }
    }

    private function scrub(string $message, string $password): string
    {
        return $password !== '' ? str_replace($password, '******', $message) : $message;
    }
}
