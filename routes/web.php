<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site routes (Module 4+ will move these to controllers)
|--------------------------------------------------------------------------
*/

Route::view('/', 'home')->name('home');

// Placeholder routes referenced by config('moaum.nav') so the nav can be
// wired up progressively in Modules 4–7 without broken links.
foreach ([
    'about.profile' => '/about',
    'about.mission' => '/about/mission-vision',
    'about.leadership' => '/about/leadership',
    'about.university' => '/about/university-relationship',
    'businesses.index' => '/businesses',
    'services.index' => '/services',
    'projects.index' => '/projects',
    'training.index' => '/training',
    'news.index' => '/news',
    'careers.index' => '/careers',
    'contact.index' => '/contact',
] as $name => $uri) {
    Route::view($uri, 'placeholder')->name($name);
}

/*
|--------------------------------------------------------------------------
| Admin placeholder routes (replaced by real auth in Module 8)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
});

// Temporary logout target until Fortify/Breeze-style auth lands in Module 8.
Route::post('/logout', fn () => redirect('/'))->name('logout');
Route::get('/login', fn () => redirect('/admin/dashboard'))->name('login');
