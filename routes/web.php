<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Livewire\Schema\Builder as SchemaBuilder;
use App\Livewire\Schema\Demo as SchemaDemo;
use App\Livewire\Schema\Index as SchemaIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::view('terms', 'legal.terms')->name('legal.terms');
Route::view('privacy', 'legal.privacy')->name('legal.privacy');

// Public contact form — embeds the ContactForm Livewire component.
Route::view('contact', 'legal.contact')->name('contact');

// SEO — robots + sitemap served dynamically so URLs always match the running host.
Route::get('robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        '',
        '# Keep authenticated, user-specific app routes out of the index',
        'Disallow: /schemas',
        'Disallow: /schemas/',
        '',
        'Sitemap: '.url('sitemap.xml'),
    ];

    return response(implode("\n", $lines)."\n")
        ->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('robots');

Route::get('sitemap.xml', function () {
    return response()
        ->view('sitemap')
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

// Public, no-account sandbox — anyone can try the builder.
Route::livewire('demo', SchemaDemo::class)->name('schemas.demo');

// Social login — Google + GitHub via Laravel Socialite. Provider is whitelisted in the controller.
Route::get('auth/{provider}/redirect', [SocialiteController::class, 'redirect'])->name('socialite.redirect');
Route::get('auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('socialite.callback');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('schemas', SchemaIndex::class)->name('schemas.index');
    Route::livewire('schemas/{project}', SchemaBuilder::class)->name('schemas.builder');
});

require __DIR__.'/settings.php';
