<?php

namespace App\Http\Controllers;

use App\Models\BusinessDivision;
use App\Models\Setting;
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
        $stats = json_decode((string) Setting::get('home.stats', '[]'), true) ?: [];

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
        ]);
    }
}
