<?php

namespace App\Http\Controllers;

use App\Models\BusinessDivision;
use App\Models\NewsArticle;
use App\Models\Project;
use App\Models\Setting;
use App\Models\TrainingProgramme;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public landing pages (Module 4). Content is database-driven:
 * divisions/services from Module 2 tables, copy from the settings table.
 */
class HomeController extends Controller
{
    public function index(Request $request): View
    {
        // Setting::get() already returns JSON-decoded arrays for json-type rows.
        $stats = Setting::get('home.stats', []);
        $stats = is_array($stats) ? $stats : (json_decode((string) $stats, true) ?: []);

        return view('home', [
            'heroBadge' => Setting::get('home.hero.badge'),
            'heroDescription' => Setting::get('home.hero.description'),
            'stats' => $stats,
            'portfolio' => [
                'eyebrow' => Setting::get('home.portfolio.eyebrow'),
                'title' => Setting::get('home.portfolio.title'),
                'description' => Setting::get('home.portfolio.description'),
            ],
            'cta' => [
                'title' => Setting::get('home.cta.title'),
                'description' => Setting::get('home.cta.description'),
            ],
            // Featured divisions for the portfolio grid + "View all" counter.
            'featuredDivisions' => BusinessDivision::query()
                ->publiclyVisible()
                ->featured()
                ->ordered()
                ->get(),
            'activeCount' => BusinessDivision::query()->publiclyVisible()->count(),
            // PRD §8 section 8 — hidden on the page until projects are published.
            'featuredProjects' => Project::query()
                ->published()
                ->featured()
                ->with('division:id,name,slug')
                ->ordered()
                ->take(3)
                ->get(),
            // PRD §8 section 9 — featured first, then the soonest upcoming.
            'trainingSpotlight' => TrainingProgramme::query()
                ->published()
                ->current()
                ->orderByDesc('featured')
                ->chronological()
                ->take(3)
                ->get(),
            // PRD §8 section 10.
            'latestNews' => NewsArticle::query()
                ->published()
                ->with('category:id,name,slug')
                ->latestFirst()
                ->take(3)
                ->get(),
        ]);
    }
}
