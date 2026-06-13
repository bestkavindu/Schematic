<?php

namespace App\Livewire\Schema;

use App\Models\SchemaColumn;
use App\Models\SchemaProject;
use App\Models\SchemaTable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::schematic')]
class Builder extends Component
{
    public SchemaProject $project;

    public function mount(SchemaProject $project): void
    {
        abort_unless($project->user_id === Auth::id(), 403);

        $this->project = $project;
    }

    /**
     * Serialize the project into the JSON shape the canvas understands.
     *
     * @return array{name: string, tables: array<int, array<string, mixed>>}
     */
    #[Computed]
    public function schema(): array
    {
        $tables = $this->project->tables()->with('columns')->get()->map(fn (SchemaTable $table): array => [
            'id' => $table->client_id,
            'name' => $table->name,
            'color' => $table->color,
            'x' => $table->pos_x,
            'y' => $table->pos_y,
            'indexes' => $table->indexes ?? [],
            'columns' => $table->columns->map(fn (SchemaColumn $column): array => [
                'id' => $column->client_id,
                'name' => $column->name,
                'type' => $column->type,
                'nullable' => $column->is_nullable,
                'pk' => $column->is_pk,
                'unique' => $column->is_unique,
                'index' => $column->is_index,
                'default' => $column->default_value ?? '',
                'fk' => $column->fk_table
                    ? [
                        'table' => $column->fk_table,
                        'column' => $column->fk_column ?? 'id',
                        'type' => $column->fk_type ?? '1:N',
                        'onDelete' => $column->fk_on_delete ?? ($column->is_nullable ? 'set null' : 'cascade'),
                        'onUpdate' => $column->fk_on_update ?? 'no action',
                    ]
                    : null,
            ])->all(),
        ])->all();

        return ['name' => $this->project->name, 'tables' => $tables];
    }

    /**
     * Persist the whole schema sent up from the canvas in one shot.
     *
     * @param  array<string, mixed>  $payload
     */
    public function save(array $payload): void
    {
        // Validate the structure for safety. Note: validate() returns only the keys it has
        // rules for, so we persist from the full $payload to keep every column attribute.
        $validator = validator($payload, [
            'name' => ['required', 'string', 'max:120'],
            'tables' => ['present', 'array'],
            'tables.*.id' => ['required', 'string', 'max:64'],
            'tables.*.name' => ['required', 'string', 'max:120'],
            'tables.*.color' => ['required', 'string', 'max:24'],
            'tables.*.x' => ['required', 'numeric'],
            'tables.*.y' => ['required', 'numeric'],
            'tables.*.indexes' => ['array'],
            'tables.*.columns' => ['present', 'array'],
            'tables.*.columns.*.id' => ['required', 'string', 'max:64'],
            'tables.*.columns.*.name' => ['required', 'string', 'max:120'],
            'tables.*.columns.*.type' => ['required', 'string', 'max:48'],
            'tables.*.columns.*.fk' => ['nullable', 'array'],
            'tables.*.columns.*.fk.table' => ['nullable', 'string', 'max:64'],
            'tables.*.columns.*.fk.column' => ['nullable', 'string', 'max:120'],
            'tables.*.columns.*.fk.type' => ['nullable', 'in:1:N,1:1'],
            'tables.*.columns.*.fk.onDelete' => ['nullable', 'in:cascade,restrict,set null,no action'],
            'tables.*.columns.*.fk.onUpdate' => ['nullable', 'in:cascade,restrict,set null,no action'],
        ]);

        // Cross-field checks the flat rules can't express: a relationship needs a target
        // column, and its target table must be one of the tables present in this payload.
        $validator->after(function ($v) use ($payload): void {
            $tableIds = collect($payload['tables'] ?? [])->pluck('id')->all();

            foreach (($payload['tables'] ?? []) as $ti => $table) {
                foreach (($table['columns'] ?? []) as $ci => $column) {
                    $fk = $column['fk'] ?? null;
                    if (! is_array($fk) || ($fk['table'] ?? null) === null) {
                        continue;
                    }

                    $base = "tables.{$ti}.columns.{$ci}.fk";

                    if (($fk['column'] ?? '') === '') {
                        $v->errors()->add("{$base}.column", 'A target column is required for a relationship.');
                    }

                    if (! in_array($fk['table'], $tableIds, true)) {
                        $v->errors()->add("{$base}.table", 'The relationship target table does not exist.');
                    }
                }
            }
        });

        $validator->validate();

        DB::transaction(function () use ($payload): void {
            $this->project->update(['name' => $payload['name']]);
            $this->project->tables()->delete();

            foreach ($payload['tables'] as $tableIndex => $tableData) {
                $table = $this->project->tables()->create([
                    'client_id' => $tableData['id'],
                    'name' => $tableData['name'],
                    'color' => $tableData['color'],
                    'pos_x' => (int) round($tableData['x']),
                    'pos_y' => (int) round($tableData['y']),
                    'indexes' => array_values($tableData['indexes'] ?? []),
                    'sort' => $tableIndex,
                ]);

                foreach (($tableData['columns'] ?? []) as $columnIndex => $columnData) {
                    $fk = $columnData['fk'] ?? null;
                    $hasFk = is_array($fk) && ($fk['table'] ?? null) !== null;
                    $nullable = (bool) ($columnData['nullable'] ?? false);

                    $table->columns()->create([
                        'client_id' => $columnData['id'],
                        'name' => $columnData['name'],
                        'type' => $columnData['type'],
                        'is_nullable' => $nullable,
                        'is_pk' => (bool) ($columnData['pk'] ?? false),
                        'is_unique' => (bool) ($columnData['unique'] ?? false),
                        'is_index' => (bool) ($columnData['index'] ?? false),
                        'default_value' => ($columnData['default'] ?? '') !== '' ? $columnData['default'] : null,
                        'fk_table' => $hasFk ? $fk['table'] : null,
                        'fk_column' => $hasFk ? ($fk['column'] ?? 'id') : null,
                        'fk_type' => $hasFk ? ($fk['type'] ?? '1:N') : null,
                        'fk_on_delete' => $hasFk ? ($fk['onDelete'] ?? ($nullable ? 'set null' : 'cascade')) : null,
                        'fk_on_update' => $hasFk ? ($fk['onUpdate'] ?? 'no action') : null,
                        'sort' => $columnIndex,
                    ]);
                }
            }
        });

        unset($this->schema);
    }

    public function render(): View
    {
        $view = view('livewire.schema.builder');
        $view->title($this->project->name);

        return $view;
    }
}
