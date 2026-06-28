<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $schema_table_id
 * @property string $client_id
 * @property string $name
 * @property string $type
 * @property int|null $size
 * @property int|null $precision
 * @property int|null $scale
 * @property bool $unsigned
 * @property bool $auto_increment
 * @property bool $is_nullable
 * @property bool $is_pk
 * @property bool $is_unique
 * @property bool $is_index
 * @property string|null $default_value
 * @property string|null $fk_table
 * @property string|null $fk_column
 * @property string|null $fk_type
 * @property string|null $fk_on_delete
 * @property string|null $fk_on_update
 * @property int $sort
 */
class SchemaColumn extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'schema_table_id', 'client_id', 'name', 'type',
        'size', 'precision', 'scale', 'unsigned', 'auto_increment',
        'is_nullable', 'is_pk',
        'is_unique', 'is_index', 'default_value', 'fk_table', 'fk_column',
        'fk_type', 'fk_on_delete', 'fk_on_update', 'sort',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'precision' => 'integer',
            'scale' => 'integer',
            'unsigned' => 'boolean',
            'auto_increment' => 'boolean',
            'is_nullable' => 'boolean',
            'is_pk' => 'boolean',
            'is_unique' => 'boolean',
            'is_index' => 'boolean',
            'sort' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<SchemaTable, $this>
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(SchemaTable::class, 'schema_table_id');
    }
}
