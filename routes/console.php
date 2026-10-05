<?php

use App\Enums\NewsStatus;
use App\Models\NewsArticle;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * PRD §16/§29 — flip scheduled news to Published once its date has passed.
 * Public visibility already follows the date, so this keeps the admin status
 * accurate (and the audit trail complete) rather than gating publication.
 */
Artisan::command('news:publish-scheduled', function () {
    $due = NewsArticle::query()
        ->where('status', NewsStatus::Scheduled)
        ->where('published_at', '<=', now())
        ->get();

    // Saved one by one so the audit observer records each transition.
    $due->each(fn (NewsArticle $article) => $article->update(['status' => NewsStatus::Published]));

    $this->info("Published {$due->count()} scheduled article(s).");
})->purpose('Mark scheduled news articles whose date has passed as published');

Schedule::command('news:publish-scheduled')->everyFiveMinutes()->withoutOverlapping();

// PRD §41 — nightly backup of the database and uploaded files.
Schedule::command('moaum:backup')->dailyAt('02:00')->withoutOverlapping()->onOneServer();

// Housekeeping: expired password-reset tokens.
Schedule::command('auth:clear-resets')->daily();
