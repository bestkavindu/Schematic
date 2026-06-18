<?php

declare(strict_types=1);

namespace App\Services\Schema;

use Illuminate\Support\ConfigurationUrlParser;
use PDO;
use RuntimeException;

/**
 * Builds a normalized Laravel `pgsql` connection config from user-supplied
 * input — either a full `postgresql://...` connection string or discrete
 * fields — and guards against connecting to private / reserved hosts (SSRF).
 */
final class PostgresConnectionFactory
{
    /** Runtime connection name registered under config('database.connections'). */
    public const NAME = 'schematic_push';

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function build(array $input): array
    {
        $url = trim((string) ($input['url'] ?? ''));

        if ($url !== '') {
            $parsed = (new ConfigurationUrlParser)->parseConfiguration(['url' => $url]);
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

            $cfg = [
                'host' => $parsed['host'] ?? null,
                'port' => $parsed['port'] ?? 5432,
                'database' => $parsed['database'] ?? 'postgres',
                'username' => $parsed['username'] ?? null,
                'password' => $parsed['password'] ?? null,
                'sslmode' => $query['sslmode'] ?? ($input['sslmode'] ?? 'require'),
            ];
        } else {
            $cfg = [
                'host' => $input['host'] ?? null,
                'port' => $input['port'] ?? 5432,
                'database' => $input['database'] ?? 'postgres',
                'username' => $input['username'] ?? null,
                'password' => $input['password'] ?? null,
                'sslmode' => $input['sslmode'] ?? 'require',
            ];
        }

        $this->guardHost((string) $cfg['host']);

        return array_merge($cfg, [
            'driver' => 'pgsql',
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => (string) ($input['schema'] ?? 'public'),
            'options' => [PDO::ATTR_TIMEOUT => 8],
        ]);
    }

    /**
     * Refuse private / reserved addresses unless explicitly allowed (for local
     * Docker testing). Blocks SSRF to internal services and cloud metadata.
     */
    private function guardHost(string $host): void
    {
        if ($host === '') {
            throw new RuntimeException('A database host is required.');
        }

        if (config('schematic.allow_private_db_hosts')) {
            return;
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP) !== false
            ? [$host]
            : (gethostbynamel($host) ?: []);

        if ($ips === []) {
            throw new RuntimeException("Could not resolve database host: {$host}.");
        }

        foreach ($ips as $ip) {
            $public = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
            if ($public === false) {
                throw new RuntimeException('Refusing to connect to a private or reserved address.');
            }
        }
    }
}
