<?php

use App\Livewire\Schema\Builder as SchemaBuilder;
use App\Livewire\Schema\Demo as SchemaDemo;
use App\Livewire\Schema\Index as SchemaIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

// Public, no-account sandbox — anyone can try the builder.
Route::livewire('demo', SchemaDemo::class)->name('schemas.demo');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('schemas', SchemaIndex::class)->name('schemas.index');
    Route::livewire('schemas/{project}', SchemaBuilder::class)->name('schemas.builder');
});

require __DIR__.'/settings.php';
