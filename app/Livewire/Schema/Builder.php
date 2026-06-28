<?php

namespace App\Livewire\Schema;

use App\Models\SchemaColumn;
use App\Models\SchemaGroup;
use App\Models\SchemaProject;
use App\Models\SchemaTable;
use App\Services\Schema\PostgresSchemaPusher;
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

    /**
     * Per-statement result of the last "push to database" attempt, or null.
     *
     * @var array{ok: bool, results: list<array{sql: string, ok: bool, error: string|null}>, warnings: list<string>, message: string}|null
     */
    public ?array $pushResult = null;

    public function mount(SchemaProject $project): void
    {
        abort_unless($project->user_id === Auth::id(), 403);

        $this->project = $project;
    }

    /**
     * Serialize the project into the JSON shape the canvas understands.
     *
     * @return array{name: string, tables: array<int, array<string, mixed>>, groups: array<int, array<string, mixed>>}
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
                'size' => $column->size,
                'precision' => $column->precision,
                'scale' => $column->scale,
                'unsigned' => $column->unsigned,
                'autoInc' => $column->auto_increment,
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

        $groups = $this->project->groups()->get()->map(fn (SchemaGroup $group): array => [
            'id' => $group->client_id,
            'name' => $group->name,
            'color' => $group->color,
            'x' => $group->pos_x,
            'y' => $group->pos_y,
            'w' => $group->width,
            'h' => $group->height,
        ])->all();

        return ['name' => $this->project->name, 'tables' => $tables, 'groups' => $groups];
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
            // Accepts both the logical vocabulary and legacy Laravel type names
            // (older clients / un-migrated projects) — normalized server-side.
            'tables.*.columns.*.type' => ['required', 'string', 'max:48'],
            'tables.*.columns.*.size' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'tables.*.columns.*.precision' => ['nullable', 'integer', 'min:0', 'max:65'],
            'tables.*.columns.*.scale' => ['nullable', 'integer', 'min:0', 'max:30'],
            'tables.*.columns.*.unsigned' => ['boolean'],
            'tables.*.columns.*.autoInc' => ['boolean'],
            'tables.*.columns.*.fk' => ['nullable', 'array'],
            'tables.*.columns.*.fk.table' => ['nullable', 'string', 'max:64'],
            'tables.*.columns.*.fk.column' => ['nullable', 'string', 'max:120'],
            'tables.*.columns.*.fk.type' => ['nullable', 'in:1:N,1:1'],
            'tables.*.columns.*.fk.onDelete' => ['nullable', 'in:cascade,restrict,set null,no action'],
            'tables.*.columns.*.fk.onUpdate' => ['nullable', 'in:cascade,restrict,set null,no action'],
            // Groups are cosmetic containers; absent on older clients, so validate only when present.
            'groups' => ['sometimes', 'array'],
            'groups.*.id' => ['required', 'string', 'max:64'],
            'groups.*.name' => ['required', 'string', 'max:120'],
            'groups.*.color' => ['required', 'string', 'max:24'],
            'groups.*.x' => ['required', 'numeric'],
            'groups.*.y' => ['required', 'numeric'],
            'groups.*.w' => ['required', 'numeric'],
            'groups.*.h' => ['required', 'numeric'],
        ]);

        // Cross-field checks the flat rules can't express: a relationship needs a target
        // column, and its target table must be one of the tables present in this payload.
        $validator->after(function ($v) use ($payload): void {
            $tableIds = collect(is_array($payload['tables'] ?? null) ? $payload['tables'] : [])->pluck('id')->all();

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
                        'size' => isset($columnData['size']) && $columnData['size'] !== '' ? (int) $columnData['size'] : null,
                        'precision' => isset($columnData['precision']) && $columnData['precision'] !== '' ? (int) $columnData['precision'] : null,
                        'scale' => isset($columnData['scale']) && $columnData['scale'] !== '' ? (int) $columnData['scale'] : null,
                        'unsigned' => (bool) ($columnData['unsigned'] ?? false),
                        'auto_increment' => (bool) ($columnData['autoInc'] ?? false),
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

            $this->project->groups()->delete();

            foreach (($payload['groups'] ?? []) as $groupIndex => $groupData) {
                $this->project->groups()->create([
                    'client_id' => $groupData['id'],
                    'name' => $groupData['name'],
                    'color' => $groupData['color'],
                    'pos_x' => (int) round($groupData['x']),
                    'pos_y' => (int) round($groupData['y']),
                    'width' => (int) round($groupData['w']),
                    'height' => (int) round($groupData['h']),
                    'sort' => $groupIndex,
                ]);
            }
        });

        unset($this->schema);
    }

    /**
     * Create this schema's tables, columns and foreign keys directly in an
     * external PostgreSQL database (e.g. Supabase). Create-only and safe:
     * existing tables are left untouched. The schema pushed is the trusted
     * server-side state ($this->schema()), never SQL sent from the browser.
     *
     * @param  array<string, mixed>  $connection
     * @param  array<string, mixed>  $options
     * @return array{ok: bool, results: list<array{sql: string, ok: bool, error: string|null}>, warnings: list<string>, message: string}
     */
    public function pushToDatabase(array $connection, array $options, PostgresSchemaPusher $pusher): array
    {
        abort_unless($this->project->user_id === Auth::id(), 403);

        $validator = validator(
            ['connection' => $connection, 'options' => $options],
            [
                'connection' => ['required', 'array'],
                'connection.url' => ['nullable', 'string', 'max:1024'],
                'connection.host' => ['nullable', 'string', 'max:255'],
                'connection.port' => ['nullable', 'integer', 'between:1,65535'],
                'connection.database' => ['nullable', 'string', 'max:128'],
                'connection.username' => ['nullable', 'string', 'max:255'],
                'connection.password' => ['nullable', 'string', 'max:512'],
                'connection.schema' => ['nullable', 'string', 'max:128'],
                'connection.sslmode' => ['nullable', 'in:disable,prefer,require,verify-ca,verify-full'],
                'options.mode' => ['required', 'in:create'],
            ],
        );

        // Need either a full connection string or, at minimum, a host and username.
        $validator->after(function ($v) use ($connection): void {
            $hasUrl = trim((string) ($connection['url'] ?? '')) !== '';
            $hasFields = trim((string) ($connection['host'] ?? '')) !== ''
                && trim((string) ($connection['username'] ?? '')) !== '';

            if (! $hasUrl && ! $hasFields) {
                $v->errors()->add('connection.url', 'Provide a connection string, or a host and username.');
            }
        });

        $validator->validate();

        $this->pushResult = $pusher->push($this->schema(), $connection, $options);

        if (! $this->pushResult['ok']) {
            $this->addError('push', $this->pushResult['message']);
        }

        return $this->pushResult;
    }

    /**
     * Toggle the project's favorite flag.
     */
    public function toggleFavorite(): void
    {
        $this->project->update(['favorite' => ! $this->project->favorite]);
    }

    /**
     * Delete the current project and return to the schema list.
     */
    public function deleteProject(): void
    {
        abort_unless($this->project->user_id === Auth::id(), 403);

        $this->project->delete();

        $this->redirectRoute('schemas.index', navigate: true);
    }

    public function render(): View
    {
        $view = view('livewire.schema.builder');
        $view->title($this->project->name);

        return $view;
    }
}
