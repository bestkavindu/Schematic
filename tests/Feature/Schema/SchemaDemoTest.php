<?php

use App\Livewire\Schema\Demo;
use Livewire\Livewire;

test('guests can open the demo builder without signing in', function () {
    $this->get(route('schemas.demo'))
        ->assertOk()
        ->assertSee('Sign up to save')
        ->assertSee('Blog Platform (demo)');
});

test('the demo renders the sample schema and guest chrome', function () {
    Livewire::test(Demo::class)
        ->assertSet('demo', true)
        ->assertSee('Sign up to save')
        ->assertSee('Blog Platform (demo)')
        ->assertDontSee('Sign out');
});
