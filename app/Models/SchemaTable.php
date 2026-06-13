<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $schema_project_id
 * @property string $client_id
 * @property string $name
 * @property string $color
 * @property int $pos_x
 * @property int $pos_y
 * @property list<string>|null $indexes
 * @property int $sort
 * @property-read Collection<int, SchemaColumn> $columns
 */
class SchemaTable extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'schema_project_id', 'client_id', 'name', 'color', 'pos_x', 'pos_y', 'indexes', 'sort',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'indexes' => 'array',
            'pos_x' => 'integer',
            'pos_y' => 'integer',
            'sort' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<SchemaProject, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(SchemaProject::class, 'schema_project_id');
    }

    /**
     * @return HasMany<SchemaColumn, $this>
     */
    public function columns(): HasMany
    {
        return $this->hasMany(SchemaColumn::class)->orderBy('sort');
    }
}
