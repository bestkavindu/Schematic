<?php

use App\Livewire\Schema\Builder;
use App\Models\SchemaProject;
use App\Models\User;
use App\Services\Schema\Contracts\SchemaPushDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * Swap in a fake driver so tests never open a real connection. It records the
 * compiled statements it was handed and returns a canned success/failure.
 */
function fakePushDriver(bool $ok = true): SchemaPushDriver
{
    $driver = new class($ok) implements SchemaPushDriver
    {
        /** @var list<string> */
        public array $received = [];

        public function __construct(private bool $ok) {}

        public function push(array $statements, array $config): array
        {
            $this->received = $statements;

            return [
                'ok' => $this->ok,
                'results' => array_map(
                    fn (string $s): array => ['sql' => $s, 'ok' => $this->ok, 'error' => $this->ok ? null : 'boom'],
                    $statements,
                ),
                'message' => $this->ok ? 'Schema pushed successfully.' : 'Push failed: boom',
            ];
        }
    };

    app()->instance(SchemaPushDriver::class, $driver);

    return $driver;
}

function pushProject(User $user): SchemaProject
{
    $project = $user->schemaProjects()->create(['name' => 'Push']);
    $table = $project->tables()->create([
        'client_id' => 't1', 'name' => 'widgets', 'color' => 'blue', 'pos_x' => 0, 'pos_y' => 0, 'sort' => 0,
    ]);
    $table->columns()->create([
        'client_id' => 'c1', 'name' => 'id', 'type' => 'id', 'is_pk' => true, 'sort' => 0,
    ]);

    return $project;
}

test('a connection-string push compiles the schema and reports success', function () {
    $user = User::factory()->create();
    $project = pushProject($user);
    $this->actingAs($user);
    config()->set('schematic.allow_private_db_hosts', true); // 127.0.0.1 is otherwise blocked

    $driver = fakePushDriver(ok: true);

    $component = Livewire::test(Builder::class, ['project' => $project])
        ->call('pushToDatabase', ['url' => 'postgresql://u:p@127.0.0.1:5432/postgres'], ['mode' => 'create'])
        ->assertHasNoErrors();

    expect($driver->received)->not->toBeEmpty();
    expect($component->instance()->pushResult['ok'])->toBeTrue();
});

test('a failed push surfaces an error and a non-ok result', function () {
    $user = User::factory()->create();
    $project = pushProject($user);
    $this->actingAs($user);
    config()->set('schematic.allow_private_db_hosts', true);

    fakePushDriver(ok: false);

    $component = Livewire::test(Builder::class, ['project' => $project])
        ->call('pushToDatabase', ['url' => 'postgresql://u:p@127.0.0.1:5432/postgres'], ['mode' => 'create'])
        ->assertHasErrors('push');

    expect($component->instance()->pushResult['ok'])->toBeFalse();
});

test('a push needs either a connection string or a host', function () {
    $user = User::factory()->create();
    $project = pushProject($user);
    $this->actingAs($user);

    Livewire::test(Builder::class, ['project' => $project])
        ->call('pushToDatabase', ['url' => ''], ['mode' => 'create'])
        ->assertHasErrors('connection.url');
});

test('the mode must be create', function () {
    $user = User::factory()->create();
    $project = pushProject($user);
    $this->actingAs($user);

    Livewire::test(Builder::class, ['project' => $project])
        ->call('pushToDatabase', ['url' => 'postgresql://u:p@127.0.0.1:5432/postgres'], ['mode' => 'drop'])
        ->assertHasErrors('options.mode');
});

test('a non-owner cannot reach the builder', function () {
    $owner = User::factory()->create();
    $project = $owner->schemaProjects()->create(['name' => 'Private']);

    $this->actingAs(User::factory()->create());

    Livewire::test(Builder::class, ['project' => $project])->assertForbidden();
});
