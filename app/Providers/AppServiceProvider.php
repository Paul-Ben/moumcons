<?php

namespace App\Providers;

use App\Models\BusinessDivision;
use App\Models\DivisionCapability;
use App\Models\Enquiry;
use App\Models\LeadershipMember;
use App\Models\Media;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\Page;
use App\Models\Project;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\TrainingProgramme;
use App\Models\User;
use App\Observers\AuditableContentObserver;
use App\Observers\AuditableTriageObserver;
use App\Services\AuditLogger;
use App\Support\AdminNav;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
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

        // CMS content: every admin create/edit/delete is diffed into the log.
        foreach ([Media::class, BusinessDivision::class, DivisionCapability::class, Service::class, ServiceCategory::class, Project::class, NewsArticle::class, NewsCategory::class, TrainingProgramme::class, Page::class, LeadershipMember::class] as $content) {
            $content::observe(AuditableContentObserver::class);
        }

        $audit = fn () => $this->app->make(AuditLogger::class);

        // Auth events are dispatched as Illuminate\Auth\Events\* objects —
        // the facade's login()/logout() methods are for authenticating users,
        // not registering listeners. Use the Event dispatcher instead (PRD §32).
        Event::listen(function (Login $event) use ($audit) {
            if ($event->user instanceof User) {
                $audit()->log('auth.login', sprintf('%s signed in%s', $event->user->name, $event->remember ? ' (remember me)' : ''), subject: $event->user);
            }
        });

        Event::listen(function (Failed $event) use ($audit) {
            $audit()->log('auth.login_failed', sprintf('Failed sign-in attempt for "%s"', $event->credentials['email'] ?? '?'));
        });

        Event::listen(function (Logout $event) use ($audit) {
            if ($event->user instanceof User) {
                $audit()->log('auth.logout', sprintf('%s signed out', $event->user->name), subject: $event->user);
            }
        });

        /*
         * The admin sidebar is shared by every admin screen, so it is resolved
         * once per render here rather than duplicated per view (PRD §24).
         */
        View::composer('components.layouts.admin', function ($view) {
            $view->with('adminNav', AdminNav::items(Auth::user()));
        });
    }
}
