<?php

declare(strict_types=1);

namespace App\Services\Schema;

use InvalidArgumentException;

/**
 * Safe PostgreSQL identifier handling. Table / column / index names and column
 * DEFAULT values originate from user input, and DDL cannot use bound
 * parameters — so they are validated and quoted/escaped here before being
 * interpolated into statements.
 */
final class PostgresIdentifier
{
    /** A valid unquoted identifier: a letter or underscore followed by word characters. */
    public const NAME = '/^[A-Za-z_][A-Za-z0-9_]*$/';

    /** DEFAULT expressions that may be emitted verbatim rather than as a string literal. */
    private const SAFE_FUNCTIONS = '/^(now\(\)|current_timestamp|gen_random_uuid\(\)|uuid_generate_v4\(\))$/i';

    public static function assertValid(string $name, string $what = 'identifier'): void
    {
        if (preg_match(self::NAME, $name) !== 1) {
            throw new InvalidArgumentException("Invalid {$what}: \"{$name}\".");
        }
    }

    public static function quote(string $name): string
    {
        return '"'.str_replace('"', '""', $name).'"';
    }

    /**
     * Render a column DEFAULT. Numbers, booleans/null and a small whitelist of
     * functions pass through; everything else becomes a single-quote-escaped
     * string literal (e.g. O'Brien -> 'O''Brien').
     */
    public static function defaultLiteral(string $raw): string
    {
        $value = trim($raw);

        if (preg_match('/^-?\d+(\.\d+)?$/', $value) === 1) {
            return $value;
        }

        if (preg_match('/^(true|false|null)$/i', $value) === 1) {
            return strtolower($value);
        }

        if (preg_match(self::SAFE_FUNCTIONS, $value) === 1) {
            return $value;
        }

        return "'".str_replace("'", "''", $value)."'";
    }
}
