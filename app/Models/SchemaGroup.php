<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A visual container that groups tables together on the canvas (purely cosmetic —
 * membership is geometric, so groups are not referenced by tables or columns).
 *
 * @property int $id
 * @property int $schema_project_id
 * @property string $client_id
 * @property string $name
 * @property string $color
 * @property int $pos_x
 * @property int $pos_y
 * @property int $width
 * @property int $height
 * @property int $sort
 */
class SchemaGroup extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'schema_project_id', 'client_id', 'name', 'color', 'pos_x', 'pos_y', 'width', 'height', 'sort',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pos_x' => 'integer',
            'pos_y' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
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
}
