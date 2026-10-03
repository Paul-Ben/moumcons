<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Public\BusinessController;
use App\Http\Controllers\Public\ServiceCatalogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Module 5 — Business Directory (PRD §9, business-detail.html prototype).
Route::get('/businesses', [BusinessController::class, 'index'])->name('businesses.index');
Route::get('/businesses/{division:slug}', [BusinessController::class, 'show'])
    ->name('businesses.show');

// Module 6 — Cross-division Services Catalogue (PRD §11).
Route::get('/services', [ServiceCatalogController::class, 'index'])->name('services.index');
Route::get('/services/{service:slug}', [ServiceCatalogController::class, 'show'])->name('services.show');

// Placeholder routes referenced by config('moaum.nav') so the nav can be
// wired up progressively in later modules without broken links.
foreach ([
    'about.profile' => '/about',
    'about.mission' => '/about/mission-vision',
    'about.leadership' => '/about/leadership',
    'about.university' => '/about/university-relationship',
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

    // Module 10 — Audit log viewer (read-only, PRD §27/§31/§32).
    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->middleware('permission:view-audit-logs')
        ->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])
        ->middleware('permission:view-audit-logs')
        ->name('audit-logs.show');
});
