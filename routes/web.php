<?php

use App\Http\Controllers\BillingController;
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

// Subscription checkout + billing portal (Polar). Guests are bounced to login
// by `auth` and returned here afterwards via the intended URL.
Route::middleware('auth')->group(function () {
    Route::get('subscribe/{cycle}', [BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::get('billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
});

require __DIR__.'/settings.php';
