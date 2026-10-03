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
| Authentication (Module 3)
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;

Route::middleware('redirect-auth')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');

    // Password reset (Laravel Password broker).
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1')->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Admin (auth-protected from Module 3 onward; screens in Modules 8–10)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::view('/dashboard', 'admin.dashboard')
        ->middleware('permission:view-admin-dashboard')
        ->name('dashboard');
});
