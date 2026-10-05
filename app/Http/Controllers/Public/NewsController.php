<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** PRD §16 — public news & updates (/news, /news/{slug}). */
class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:100'],
        ]);

        $categories = NewsCategory::query()
            ->whereHas('articles', fn ($q) => $q->published())
            ->ordered()
            ->get();

        $activeCategory = $categories->firstWhere('slug', $filters['category'] ?? null);
        $filtering = $activeCategory || filled($filters['tag'] ?? null);

        $query = NewsArticle::query()
            ->published()
            ->with('category:id,name,slug')
            ->when($activeCategory, fn ($q) => $q->where('news_category_id', $activeCategory->id))
            ->when($filters['tag'] ?? null, fn ($q, $tag) => $q->whereJsonContains('tags', $tag));

        // The lead story is the newest featured article on the unfiltered first page.
        $lead = (! $filtering && $request->integer('page', 1) === 1)
            ? (clone $query)->where('featured', true)->latestFirst()->first()
            : null;

        $articles = $query
            ->when($lead, fn ($q) => $q->whereKeyNot($lead->id))
            ->latestFirst()
            ->paginate(9)
            ->withQueryString();

        return view('public.news.index', [
            'lead' => $lead,
            'articles' => $articles,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'activeTag' => $filters['tag'] ?? null,
        ]);
    }

    public function show(NewsArticle $article): View
    {
        abort_unless($article->isPublished(), 404);

        $article->load(['category', 'author:id,name']);

        $related = NewsArticle::query()
            ->published()
            ->whereKeyNot($article->id)
            ->when($article->news_category_id, fn ($q, $id) => $q->where('news_category_id', $id))
            ->latestFirst()
            ->take(3)
            ->get();

        return view('public.news.show', ['article' => $article, 'related' => $related]);
    }
}
