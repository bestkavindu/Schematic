<?php

declare(strict_types=1);

namespace App\Services\Schema;

/**
 * Server-side mirror of resources/js/types.js.
 *
 * The schema's stored type vocabulary is the neutral logical set (int64, varchar,
 * decimal, ...) plus structured attributes (unsigned / auto_increment / size /
 * precision / scale). toLaravel() collapses a column back to the nearest legacy
 * Laravel type name so the existing Postgres compiler / type maps keep producing
 * identical DDL — for both logical columns and un-migrated rows that still hold a
 * legacy name. Keep this in lock-step with toLegacyLaravelType() in types.js
 * (a golden-DDL test guards the pair).
 */
final class LogicalType
{
    /** The 14 legacy Laravel type names (pre-neutral vocabulary). */
    private const LEGACY = [
        'id', 'bigInteger', 'unsignedBigInteger', 'integer', 'string', 'text',
        'boolean', 'date', 'datetime', 'timestamp', 'json', 'decimal', 'float', 'uuid',
    ];

    /**
     * Collapse a column (logical or legacy) to the nearest legacy Laravel type name.
     *
     * @param  array<string, mixed>  $column
     */
    public static function toLaravel(array $column): string
    {
        $type = (string) ($column['type'] ?? '');

        if (in_array($type, self::LEGACY, true)) {
            return $type; // already a legacy name (un-migrated row) — identity
        }

        $unsigned = ! empty($column['unsigned']);
        $autoInc = ! empty($column['autoInc']) || ! empty($column['auto_increment']);

        return match ($type) {
            'int64' => $autoInc ? 'id' : ($unsigned ? 'unsignedBigInteger' : 'bigInteger'),
            'int32', 'int16', 'int8' => $autoInc ? 'id' : 'integer',
            'varchar', 'char' => 'string',
            'text', 'binary' => 'text',
            'bool' => 'boolean',
            'date' => 'date',
            'time', 'datetime' => 'datetime',
            'timestamp', 'timestamptz' => 'timestamp',
            'json' => 'json',
            'decimal' => 'decimal',
            'float32', 'float64' => 'float',
            'uuid' => 'uuid',
            'enum' => 'string',
            default => 'string',
        };
    }
}
