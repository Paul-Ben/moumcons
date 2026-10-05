<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\SiteSearch;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** PRD §37 — /search across public content. */
class SearchController extends Controller
{
    public function __invoke(Request $request, SiteSearch $search): View
    {
        $term = trim((string) ($request->validate(['q' => ['nullable', 'string', 'max:100']])['q'] ?? ''));

        $groups = $search->search($term, $request->user());

        return view('public.search', [
            'term' => $term,
            'groups' => $groups,
            'total' => $groups->sum(fn ($group) => $group['results']->count()),
        ]);
    }
}
