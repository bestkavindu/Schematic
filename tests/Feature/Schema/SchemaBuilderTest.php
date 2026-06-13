<?php

use App\Livewire\Schema\Builder;
use App\Livewire\Schema\Index;
use App\Models\SchemaProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

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
