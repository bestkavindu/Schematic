<?php

use App\Livewire\Schema\Builder as SchemaBuilder;
use App\Livewire\Schema\Index as SchemaIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('schemas', SchemaIndex::class)->name('schemas.index');
    Route::livewire('schemas/{project}', SchemaBuilder::class)->name('schemas.builder');
});

require __DIR__.'/settings.php';
