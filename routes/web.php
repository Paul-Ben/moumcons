<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BusinessDivisionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\LeadershipMemberController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\QuoteRequestController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ServiceRequestController;
use App\Http\Controllers\Admin\TrainingProgrammeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Public\BusinessController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\DownloadController;
use App\Http\Controllers\Public\FaqController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\ProjectController;
use App\Http\Controllers\Public\ServiceCatalogController;
use App\Http\Controllers\Public\TrainingController;
use App\Http\Controllers\Requests\RequestQuoteController;
use App\Http\Controllers\Requests\RequestServiceController;
use App\Http\Controllers\Requests\TrackRequestController;
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

// M4 — Project portfolio (PRD §14).
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');

// M5 — News & updates (PRD §16).
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{article:slug}', [NewsController::class, 'show'])->name('news.show');

// M6 — Training programmes (PRD §15). Interest registrations become enquiries.
Route::get('/training', [TrainingController::class, 'index'])->name('training.index');
Route::get('/training/{programme:slug}', [TrainingController::class, 'show'])->name('training.show');
Route::post('/training/{programme:slug}/interest', [TrainingController::class, 'registerInterest'])
    ->middleware('throttle:6,1')
    ->name('training.interest');

// M8 — Downloads (PRD §18), FAQs (§22) and gallery (§19). Downloads are
// access-checked and counted; files are never served directly.
Route::get('/downloads', [DownloadController::class, 'index'])->name('downloads.index');
Route::get('/downloads/{document:slug}', [DownloadController::class, 'download'])
    ->middleware('throttle:60,1')
    ->name('downloads.show');
Route::get('/faqs', [FaqController::class, 'index'])->name('faqs.index');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/{gallery:slug}', [GalleryController::class, 'show'])->name('gallery.show');

// Module 7 — Customer engagement: service + quote requests, public tracking
// (PRD §12/§13). Forms are public; protected by rate limiting + honeypot.
Route::get('/request-service', [RequestServiceController::class, 'create'])->name('requests.service.create');
Route::post('/request-service', [RequestServiceController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('requests.service.store');
Route::get('/request-service/{reference}', [RequestServiceController::class, 'confirmation'])
    ->name('requests.service.confirmation');

Route::get('/request-quote', [RequestQuoteController::class, 'create'])->name('requests.quote.create');
Route::post('/request-quote', [RequestQuoteController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('requests.quote.store');
Route::get('/request-quote/{reference}', [RequestQuoteController::class, 'confirmation'])
    ->name('requests.quote.confirmation');

// PRD §12/§13 — status tracking by reference + email (no account needed).
Route::get('/track-request/{reference?}', [TrackRequestController::class, 'index'])->name('requests.track');
Route::post('/track-request', [TrackRequestController::class, 'show'])
    ->middleware('throttle:10,1')
    ->name('requests.track.lookup');

// Placeholder routes referenced by config('moaum.nav') so the nav can be
// wired up progressively in later modules without broken links.
// M7 — CMS pages (PRD §9/§22). System pages resolve by key (App\Models\Page::SYSTEM).
foreach ([
    'about.profile' => ['/about', 'about'],
    'about.mission' => ['/about/mission-vision', 'mission-vision'],
    'about.leadership' => ['/about/leadership', 'leadership'],
    'about.university' => ['/about/university-relationship', 'university-relationship'],
    'legal.privacy' => ['/privacy-policy', 'privacy-policy'],
    'legal.terms' => ['/terms', 'terms'],
] as $name => [$uri, $key]) {
    Route::get($uri, [PageController::class, 'system'])->defaults('key', $key)->name($name);
}
Route::get('/pages/{page:slug}', [PageController::class, 'show'])->name('pages.show');

foreach ([
    'careers.index' => '/careers',
] as $name => $uri) {
    Route::view($uri, 'placeholder')->name($name);
}

/*
|--------------------------------------------------------------------------
| Contact & Enquiries (Module 3, PRD §20/§21)
|--------------------------------------------------------------------------
*/

Route::get('/contact', [ContactController::class, 'create'])->name('contact.index');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('contact.store');
// The confirmation page echoes the visitor's name, email and message, so it is
// reachable only through the signed link issued at submission time — a bare
// reference is guessable and must not expose enquiry contents.
Route::get('/contact/received/{reference}', [ContactController::class, 'confirmation'])
    ->middleware(['signed', 'throttle:30,1'])
    ->name('contact.confirmation');

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
    // Module 2 - Administration Dashboard (PRD §24).
    Route::get('/', fn () => redirect()->route('admin.dashboard'));

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:view-admin-dashboard')
        ->name('dashboard');

    // Module 4 — Enquiry triage queue (PRD §21, route listed in §31).
    // Viewing is one permission; progression, ownership and closure are separate
    // so the screens and UpdateEnquiryRequest can gate them independently (§23).
    Route::get('/enquiries', [EnquiryController::class, 'index'])
        ->middleware('permission:view-enquiries')
        ->name('enquiries.index');
    Route::get('/enquiries/{enquiry}', [EnquiryController::class, 'show'])
        ->middleware('permission:view-enquiries')
        ->name('enquiries.show');
    Route::get('/enquiries/{enquiry}/attachment', [EnquiryController::class, 'downloadAttachment'])
        ->middleware('permission:view-enquiries')
        ->name('enquiries.attachment');
    // Triage writes are authorised per field by UpdateEnquiryRequest, so the
    // route only guards visibility of the enquiry itself.
    Route::patch('/enquiries/{enquiry}', [EnquiryController::class, 'update'])
        ->middleware('permission:view-enquiries')
        ->name('enquiries.update');

    // M1 — Service request triage (PRD §12). Writes are authorised by
    // UpdateServiceRequestRequest; the route guards visibility only.
    Route::middleware('permission:view-service-requests')->group(function () {
        Route::get('/service-requests', [ServiceRequestController::class, 'index'])->name('service-requests.index');
        Route::get('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'show'])->name('service-requests.show');
        Route::get('/service-requests/{serviceRequest}/attachment', [ServiceRequestController::class, 'downloadAttachment'])->name('service-requests.attachment');
        Route::patch('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'update'])->name('service-requests.update');
    });

    // M1 — Quote requests (PRD §13). UpdateQuoteRequestRequest splits
    // progression, quote preparation and settlement by permission.
    Route::middleware('permission:view-quotes')->group(function () {
        Route::get('/quote-requests', [QuoteRequestController::class, 'index'])->name('quote-requests.index');
        Route::get('/quote-requests/{quoteRequest}', [QuoteRequestController::class, 'show'])->name('quote-requests.show');
        Route::get('/quote-requests/{quoteRequest}/attachment', [QuoteRequestController::class, 'downloadAttachment'])->name('quote-requests.attachment');
        Route::patch('/quote-requests/{quoteRequest}', [QuoteRequestController::class, 'update'])->name('quote-requests.update');
    });

    // M3 — Business divisions (PRD §10, /admin/businesses in §31) and the
    // services catalogue (§11). Per-action checks live in the policies and
    // form requests; the group guards visibility.
    Route::middleware('permission:view-divisions')->group(function () {
        Route::resource('businesses', BusinessDivisionController::class)
            ->except('show')
            ->parameters(['businesses' => 'division'])
            ->names('divisions');
    });

    Route::middleware('permission:view-services')->group(function () {
        Route::resource('services', ServiceController::class)->except('show');
        Route::resource('service-categories', ServiceCategoryController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['service-categories' => 'category']);
    });

    // M4 — Projects (PRD §14).
    Route::middleware('permission:view-projects')->group(function () {
        Route::resource('projects', AdminProjectController::class)->except('show');
    });

    // M5 — News (PRD §16).
    Route::middleware('permission:view-news')->group(function () {
        Route::resource('news', AdminNewsController::class)->except('show')->parameters(['news' => 'article']);
        Route::resource('news-categories', NewsCategoryController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['news-categories' => 'category']);
    });

    // M6 — Training (PRD §15, /admin/training in §31).
    Route::middleware('permission:view-training')->group(function () {
        Route::resource('training', TrainingProgrammeController::class)->except('show')->parameters(['training' => 'programme']);
    });

    // M7 — Pages and the leadership team (PRD §9/§22).
    Route::middleware('permission:view-pages')->group(function () {
        Route::resource('pages', AdminPageController::class)->except('show');
        Route::resource('leadership', LeadershipMemberController::class)->except('show')->parameters(['leadership' => 'member']);
    });

    // M8 — Downloads, FAQs, gallery.
    Route::middleware('permission:view-downloads')->group(function () {
        Route::resource('documents', DocumentController::class)->except('show');
        Route::get('/documents/{document}/file', [DocumentController::class, 'file'])->name('documents.file');
    });
    Route::middleware('permission:view-faqs')->group(function () {
        Route::resource('faqs', AdminFaqController::class)->except('show');
    });
    Route::middleware('permission:view-gallery')->group(function () {
        Route::resource('galleries', AdminGalleryController::class)->except('show');
    });

    // M2 — Media library (PRD §19/§22). Per-action checks live in MediaPolicy;
    // the JSON variants back the media picker and Trix uploads.
    Route::middleware('permission:view-media')->group(function () {
        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::patch('/media/{media}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
    });
    // Upload is gated on its own permission so an uploader need not browse.
    Route::post('/media', [MediaController::class, 'store'])
        ->middleware(['permission:upload-media', 'throttle:60,1'])
        ->name('media.store');

    // Module 10 — Audit log viewer (read-only, PRD §27/§31/§32).
    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->middleware('permission:view-audit-logs')
        ->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])
        ->middleware('permission:view-audit-logs')
        ->name('audit-logs.show');
});
