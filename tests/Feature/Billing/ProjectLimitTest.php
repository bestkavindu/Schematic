<?php

use App\Livewire\Schema\Index;
use App\Models\SchemaProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * Give a user an active Polar subscription so subscribed() returns true.
 */
function subscribe(User $user): User
{
    $user->subscriptions()->create([
        'type' => 'default',
        'polar_id' => 'sub_'.$user->id,
        'status' => 'active',
        'product_id' => 'prod_team',
    ]);

    return $user;
}

test('a free user can create projects below the limit', function () {
    $user = User::factory()->create();
    $user->schemaProjects()->create(['name' => 'One']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('newProject')
        ->assertRedirect();

    expect($user->schemaProjects()->count())->toBe(2);
});

test('a free user is blocked at the project limit', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < User::FREE_PROJECT_LIMIT; $i++) {
        $user->schemaProjects()->create(['name' => "Project {$i}"]);
    }

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('newProject')
        ->assertSet('showLimitModal', true)
        ->assertNoRedirect();

    expect($user->schemaProjects()->count())->toBe(User::FREE_PROJECT_LIMIT);
});

test('a subscribed user has no project limit', function () {
    $user = subscribe(User::factory()->create());

    for ($i = 0; $i < User::FREE_PROJECT_LIMIT; $i++) {
        $user->schemaProjects()->create(['name' => "Project {$i}"]);
    }

    expect($user->canCreateProject())->toBeTrue()
        ->and($user->projectLimit())->toBeNull();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('newProject')
        ->assertRedirect();

    expect($user->schemaProjects()->count())->toBe(User::FREE_PROJECT_LIMIT + 1);
});

test('canCreateProject reflects the free limit', function () {
    $user = User::factory()->create();
    expect($user->canCreateProject())->toBeTrue();

    for ($i = 0; $i < User::FREE_PROJECT_LIMIT; $i++) {
        $user->schemaProjects()->create(['name' => "Project {$i}"]);
    }

    expect($user->fresh()->canCreateProject())->toBeFalse();
});
