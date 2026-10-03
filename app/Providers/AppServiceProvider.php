<?php

namespace App\Providers;

use App\Models\Enquiry;
use App\Models\QuoteRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Observers\AuditableTriageObserver;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * Audit trail (PRD §32). Triage models are observed for create /
         * update / delete; authentication events are logged directly.
         */
        Enquiry::observe(AuditableTriageObserver::class);
        ServiceRequest::observe(AuditableTriageObserver::class);
        QuoteRequest::observe(AuditableTriageObserver::class);

        $audit = fn () => $this->app->make(AuditLogger::class);

        Auth::login(function (User $user, bool $remember) use ($audit) {
            $audit()->log('auth.login', sprintf('%s signed in%s', $user->name, $remember ? ' (remember me)' : ''), subject: $user);
        });

        Auth::failed(function (array $credentials) use ($audit) {
            $audit()->log('auth.login_failed', sprintf('Failed sign-in attempt for "%s"', $credentials['email'] ?? '?'));
        });

        Auth::logout(function (User $user) use ($audit) {
            $audit()->log('auth.logout', sprintf('%s signed out', $user->name), subject: $user);
        });
    }
}
