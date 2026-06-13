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
                    ? ['table' => $column->fk_table, 'column' => $column->fk_column ?? 'id']
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
        validator($payload, [
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
        ])->validate();

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

                    $table->columns()->create([
                        'client_id' => $columnData['id'],
                        'name' => $columnData['name'],
                        'type' => $columnData['type'],
                        'is_nullable' => (bool) ($columnData['nullable'] ?? false),
                        'is_pk' => (bool) ($columnData['pk'] ?? false),
                        'is_unique' => (bool) ($columnData['unique'] ?? false),
                        'is_index' => (bool) ($columnData['index'] ?? false),
                        'default_value' => ($columnData['default'] ?? '') !== '' ? $columnData['default'] : null,
                        'fk_table' => is_array($fk) ? ($fk['table'] ?? null) : null,
                        'fk_column' => is_array($fk) ? ($fk['column'] ?? 'id') : null,
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
