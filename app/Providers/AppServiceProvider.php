<?php

namespace App\Providers;

use App\Models\Enquiry;
use App\Models\QuoteRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Observers\AuditableTriageObserver;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
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

        // Auth events are dispatched as Illuminate\Auth\Events\* objects —
        // the facade's login()/logout() methods are for authenticating users,
        // not registering listeners. Use the Event dispatcher instead (PRD §32).
        Event::listen(function (\Illuminate\Auth\Events\Login $event) use ($audit) {
            if ($event->user instanceof User) {
                $audit()->log('auth.login', sprintf('%s signed in%s', $event->user->name, $event->remember ? ' (remember me)' : ''), subject: $event->user);
            }
        });

        Event::listen(function (\Illuminate\Auth\Events\Failed $event) use ($audit) {
            $audit()->log('auth.login_failed', sprintf('Failed sign-in attempt for "%s"', $event->credentials['email'] ?? '?'));
        });

        Event::listen(function (\Illuminate\Auth\Events\Logout $event) use ($audit) {
            if ($event->user instanceof User) {
                $audit()->log('auth.logout', sprintf('%s signed out', $event->user->name), subject: $event->user);
            }
        });
    }
}
