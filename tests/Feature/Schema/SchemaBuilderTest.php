<?php

use App\Livewire\Schema\Builder;
use App\Livewire\Schema\Index;
use App\Models\SchemaProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * Build a save payload for a two-table schema where `child.parent_id` carries
 * the given fk array. Lets the relationship tests vary just the fk.
 */
function relationshipPayload(array $fk, bool $childNullable = false): array
{
    return [
        'name' => 'Rel',
        'tables' => [
            [
                'id' => 't_parent', 'name' => 'parents', 'color' => 'blue', 'x' => 0, 'y' => 0, 'indexes' => [],
                'columns' => [
                    ['id' => 'p1', 'name' => 'id', 'type' => 'id', 'nullable' => false, 'pk' => true, 'unique' => false, 'index' => false, 'default' => '', 'fk' => null],
                ],
            ],
            [
                'id' => 't_child', 'name' => 'children', 'color' => 'green', 'x' => 300, 'y' => 0, 'indexes' => [],
                'columns' => [
                    ['id' => 'c1', 'name' => 'id', 'type' => 'id', 'nullable' => false, 'pk' => true, 'unique' => false, 'index' => false, 'default' => '', 'fk' => null],
                    ['id' => 'c2', 'name' => 'parent_id', 'type' => 'unsignedBigInteger', 'nullable' => $childNullable, 'pk' => false, 'unique' => false, 'index' => true, 'default' => '', 'fk' => $fk],
                ],
            ],
        ],
    ];
}

test('the schemas dashboard is displayed', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('schemas.index'))->assertOk()->assertSee('Your schemas');
});

test('a user can open their own schema in the builder', function () {
    $user = User::factory()->create();
    $project = $user->schemaProjects()->create(['name' => 'My Schema']);

    $this->actingAs($user)
        ->get(route('schemas.builder', $project))
        ->assertOk();
});

test('a user cannot open another users schema', function () {
    $owner = User::factory()->create();
    $project = $owner->schemaProjects()->create(['name' => 'Private']);

    $this->actingAs(User::factory()->create())
        ->get(route('schemas.builder', $project))
        ->assertForbidden();
});

test('creating a new project redirects to the builder', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->call('newProject')
        ->assertRedirect();

    expect($user->schemaProjects()->count())->toBe(1);
    expect($user->schemaProjects()->first()->name)->toBe('Untitled Schema');
});

test('saving persists tables, columns and relationships', function () {
    $user = User::factory()->create();
    $project = $user->schemaProjects()->create(['name' => 'Draft']);

    $payload = [
        'name' => 'Inventory',
        'tables' => [[
            'id' => 't_widgets',
            'name' => 'widgets',
            'color' => 'teal',
            'x' => 120,
            'y' => 80,
            'indexes' => ['widgets_owner_id_index'],
            'columns' => [
                ['id' => 'c1', 'name' => 'id', 'type' => 'id', 'nullable' => false, 'pk' => true, 'unique' => false, 'index' => false, 'default' => '', 'fk' => null],
                ['id' => 'c2', 'name' => 'owner_id', 'type' => 'unsignedBigInteger', 'nullable' => false, 'pk' => false, 'unique' => false, 'index' => true, 'default' => '', 'fk' => ['table' => 't_widgets', 'column' => 'id']],
            ],
        ]],
    ];

    $this->actingAs($user);

    Livewire::test(Builder::class, ['project' => $project])
        ->call('save', $payload)
        ->assertHasNoErrors();

    $project->refresh();
    expect($project->name)->toBe('Inventory');
    expect($project->tables()->count())->toBe(1);

    $table = $project->tables()->first();
    expect($table->name)->toBe('widgets');
    expect($table->color)->toBe('teal');
    expect($table->pos_x)->toBe(120);
    expect($table->indexes)->toBe(['widgets_owner_id_index']);
    expect($table->columns()->count())->toBe(2);

    $fk = $table->columns()->where('client_id', 'c2')->first();
    expect($fk->is_index)->toBeTrue();
    expect($fk->fk_table)->toBe('t_widgets');
    expect($fk->fk_column)->toBe('id');
});

test('saving replaces the previous schema rather than appending', function () {
    $user = User::factory()->create();
    $project = $user->schemaProjects()->create(['name' => 'Draft']);

    $payload = fn (string $tableName) => [
        'name' => 'Draft',
        'tables' => [[
            'id' => 't_a', 'name' => $tableName, 'color' => 'blue', 'x' => 0, 'y' => 0, 'indexes' => [],
            'columns' => [
                ['id' => 'c1', 'name' => 'id', 'type' => 'id', 'nullable' => false, 'pk' => true, 'unique' => false, 'index' => false, 'default' => '', 'fk' => null],
            ],
        ]],
    ];

    $this->actingAs($user);

    Livewire::test(Builder::class, ['project' => $project])
        ->call('save', $payload('first'))
        ->call('save', $payload('second'));

    $project->refresh();
    expect($project->tables()->count())->toBe(1);
    expect($project->tables()->first()->name)->toBe('second');
});

test('the schema_columns table has the relationship-meta columns', function () {
    expect(Schema::hasColumns('schema_columns', ['fk_type', 'fk_on_delete', 'fk_on_update']))->toBeTrue();
});

test('saving preserves full relationship metadata', function () {
    $user = User::factory()->create();
    $project = $user->schemaProjects()->create(['name' => 'Rel']);
    $this->actingAs($user);

    $fk = ['table' => 't_parent', 'column' => 'id', 'type' => '1:1', 'onDelete' => 'set null', 'onUpdate' => 'cascade'];

    Livewire::test(Builder::class, ['project' => $project])
        ->call('save', relationshipPayload($fk, childNullable: true))
        ->assertHasNoErrors();

    $col = $project->tables()->where('client_id', 't_child')->first()
        ->columns()->where('client_id', 'c2')->first();

    expect($col->fk_table)->toBe('t_parent');
    expect($col->fk_column)->toBe('id');
    expect($col->fk_type)->toBe('1:1');
    expect($col->fk_on_delete)->toBe('set null');
    expect($col->fk_on_update)->toBe('cascade');
});

test('relationship referential actions default from nullability when omitted', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Non-nullable FK -> cascade on delete.
    $project = $user->schemaProjects()->create(['name' => 'Rel']);
    Livewire::test(Builder::class, ['project' => $project])
        ->call('save', relationshipPayload(['table' => 't_parent', 'column' => 'id']))
        ->assertHasNoErrors();
    $col = $project->tables()->where('client_id', 't_child')->first()->columns()->where('client_id', 'c2')->first();
    expect($col->fk_type)->toBe('1:N');
    expect($col->fk_on_delete)->toBe('cascade');
    expect($col->fk_on_update)->toBe('no action');

    // Nullable FK -> set null on delete.
    $project2 = $user->schemaProjects()->create(['name' => 'Rel2']);
    Livewire::test(Builder::class, ['project' => $project2])
        ->call('save', relationshipPayload(['table' => 't_parent', 'column' => 'id'], childNullable: true))
        ->assertHasNoErrors();
    $col2 = $project2->tables()->where('client_id', 't_child')->first()->columns()->where('client_id', 'c2')->first();
    expect($col2->fk_on_delete)->toBe('set null');
});

test('legacy fk rows without meta rehydrate with defaults', function () {
    $user = User::factory()->create();
    $project = $user->schemaProjects()->create(['name' => 'Legacy']);
    $table = $project->tables()->create(['client_id' => 't_a', 'name' => 'a', 'color' => 'blue', 'pos_x' => 0, 'pos_y' => 0, 'sort' => 0]);
    // A column saved before the meta columns existed: fk_table set, meta NULL.
    $table->columns()->create([
        'client_id' => 'c1', 'name' => 'parent_id', 'type' => 'unsignedBigInteger',
        'is_nullable' => false, 'fk_table' => 't_a', 'fk_column' => 'id', 'sort' => 0,
    ]);

    $this->actingAs($user);
    $schema = Livewire::test(Builder::class, ['project' => $project])->instance()->schema();
    $fk = $schema['tables'][0]['columns'][0]['fk'];

    expect($fk['type'])->toBe('1:N');
    expect($fk['onDelete'])->toBe('cascade');
    expect($fk['onUpdate'])->toBe('no action');
});

test('saving rejects an invalid relationship type', function () {
    $user = User::factory()->create();
    $project = $user->schemaProjects()->create(['name' => 'Rel']);
    $this->actingAs($user);

    Livewire::test(Builder::class, ['project' => $project])
        ->call('save', relationshipPayload(['table' => 't_parent', 'column' => 'id', 'type' => 'bogus']))
        ->assertHasErrors();
});

test('saving rejects a relationship pointing at a missing table', function () {
    $user = User::factory()->create();
    $project = $user->schemaProjects()->create(['name' => 'Rel']);
    $this->actingAs($user);

    Livewire::test(Builder::class, ['project' => $project])
        ->call('save', relationshipPayload(['table' => 't_nope', 'column' => 'id']))
        ->assertHasErrors();
});

test('saving rejects a relationship without a target column', function () {
    $user = User::factory()->create();
    $project = $user->schemaProjects()->create(['name' => 'Rel']);
    $this->actingAs($user);

    Livewire::test(Builder::class, ['project' => $project])
        ->call('save', relationshipPayload(['table' => 't_parent', 'column' => '']))
        ->assertHasErrors();
});

test('saving persists groups and the schema round-trips them', function () {
    $user = User::factory()->create();
    $project = $user->schemaProjects()->create(['name' => 'Grouped']);
    $this->actingAs($user);

    $payload = [
        'name' => 'Grouped',
        'tables' => [],
        'groups' => [
            ['id' => 'g1', 'name' => 'Product', 'color' => 'green', 'x' => 80, 'y' => 60, 'w' => 420, 'h' => 300],
            ['id' => 'g2', 'name' => 'Billing', 'color' => 'amber', 'x' => 560.4, 'y' => 60, 'w' => 360, 'h' => 280],
        ],
    ];

    Livewire::test(Builder::class, ['project' => $project])
        ->call('save', $payload)
        ->assertHasNoErrors();

    $project->refresh();
    expect($project->groups()->count())->toBe(2);

    $product = $project->groups()->where('client_id', 'g1')->first();
    expect($product->name)->toBe('Product');
    expect($product->color)->toBe('green');
    expect($product->pos_x)->toBe(80);
    expect($product->width)->toBe(420);

    // Float positions are rounded to ints on the way in.
    expect($project->groups()->where('client_id', 'g2')->first()->pos_x)->toBe(560);

    $schema = Livewire::test(Builder::class, ['project' => $project])->instance()->schema();
    expect($schema['groups'])->toHaveCount(2);
    expect($schema['groups'][0])->toMatchArray(['id' => 'g1', 'name' => 'Product', 'x' => 80, 'w' => 420]);
});

test('saving a payload without groups leaves none and does not error', function () {
    $user = User::factory()->create();
    $project = $user->schemaProjects()->create(['name' => 'NoGroups']);
    $this->actingAs($user);

    Livewire::test(Builder::class, ['project' => $project])
        ->call('save', ['name' => 'NoGroups', 'tables' => []])
        ->assertHasNoErrors();

    expect($project->refresh()->groups()->count())->toBe(0);
});
