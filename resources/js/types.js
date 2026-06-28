/*
 * Neutral logical type system — the single source of truth for column types.
 *
 * Schematic's stored type vocabulary used to be Laravel/Eloquent method names
 * ('bigInteger', 'unsignedBigInteger', 'id', ...), which baked one platform into
 * the data model. This module replaces that with a platform-agnostic logical
 * vocabulary plus structured attributes (size / precision / scale / unsigned /
 * autoIncrement). Per-target type mapping (SQL dialects, ORMs) is layered on top
 * of this in later phases.
 *
 * Backward compatibility is non-negotiable: every project ever saved stored a
 * legacy type string, and users hold JSON exports with them. normalizeLegacyType()
 * is therefore a PERMANENT shim, and toLegacyLaravelType() lets the existing
 * exporters keep working unchanged by collapsing a logical column back to the
 * nearest legacy name. Both are mirrored in app/Services/Schema/LogicalType.php —
 * keep the two in lock-step (a golden-DDL test guards the pair).
 */

// Ordered for the type picker. label drives the on-canvas badge.
export const LOGICAL_META = {
    int8: { label: 'tinyint', canUnsigned: true, canAutoInc: true },
    int16: { label: 'smallint', canUnsigned: true, canAutoInc: true },
    int32: { label: 'int', canUnsigned: true, canAutoInc: true },
    int64: { label: 'bigint', canUnsigned: true, canAutoInc: true },
    float32: { label: 'float' },
    float64: { label: 'double' },
    decimal: { label: 'decimal', hasPrecisionScale: true },
    bool: { label: 'boolean' },
    char: { label: 'char', hasSize: true },
    varchar: { label: 'varchar', hasSize: true },
    text: { label: 'text' },
    date: { label: 'date' },
    time: { label: 'time' },
    datetime: { label: 'datetime' },
    timestamp: { label: 'timestamp' },
    timestamptz: { label: 'timestamptz' },
    json: { label: 'json' },
    uuid: { label: 'uuid' },
    binary: { label: 'binary' },
    enum: { label: 'enum' },
};

export const LOGICAL_TYPES = Object.keys(LOGICAL_META);

// Legacy Laravel type name → logical type + default attributes.
const LEGACY_MAP = {
    id: { type: 'int64', autoInc: true, unsigned: true },
    bigInteger: { type: 'int64' },
    unsignedBigInteger: { type: 'int64', unsigned: true },
    integer: { type: 'int32' },
    string: { type: 'varchar', size: 255 },
    text: { type: 'text' },
    boolean: { type: 'bool' },
    date: { type: 'date' },
    datetime: { type: 'datetime' },
    timestamp: { type: 'timestamp' },
    json: { type: 'json' },
    decimal: { type: 'decimal', precision: 8, scale: 2 },
    float: { type: 'float64' },
    uuid: { type: 'uuid' },
};

const LEGACY_SET = new Set(Object.keys(LEGACY_MAP));

/**
 * Resolve any stored type string (legacy name or already-logical) to a logical
 * type + its default attributes. Idempotent on logical inputs.
 * @returns {{type:string,size?:number,precision?:number,scale?:number,unsigned?:boolean,autoInc?:boolean}}
 */
export function normalizeLegacyType(old) {
    const key = String(old || '');
    if (LEGACY_MAP[key]) return { ...LEGACY_MAP[key] };
    if (LOGICAL_META[key]) return { type: key };
    return { type: 'varchar', size: 255 }; // unknown → safe default
}

/**
 * Collapse a logical column back to the nearest legacy Laravel type name, so the
 * existing legacy-keyed exporters / Postgres compiler keep producing identical
 * output for untouched columns. Legacy names pass through unchanged.
 */
export function toLegacyLaravelType(col) {
    const type = String((col && col.type) || '');
    if (LEGACY_SET.has(type)) return type; // old row stored a legacy name — identity

    const unsigned = !!(col && col.unsigned);
    const autoInc = !!(col && col.autoInc);

    switch (type) {
        case 'int64': return autoInc ? 'id' : (unsigned ? 'unsignedBigInteger' : 'bigInteger');
        case 'int32':
        case 'int16':
        case 'int8': return autoInc ? 'id' : 'integer';
        case 'varchar':
        case 'char': return 'string';
        case 'text':
        case 'binary': return 'text';
        case 'bool': return 'boolean';
        case 'date': return 'date';
        case 'time':
        case 'datetime': return 'datetime';
        case 'timestamp':
        case 'timestamptz': return 'timestamp';
        case 'json': return 'json';
        case 'decimal': return 'decimal';
        case 'float32':
        case 'float64': return 'float';
        case 'uuid': return 'uuid';
        case 'enum': return 'string';
        default: return 'string';
    }
}
