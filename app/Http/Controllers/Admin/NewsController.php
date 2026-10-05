<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NewsStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\NewsArticleRequest;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin → News & announcements (PRD §16, /admin/news in §31). */
class NewsController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', NewsArticle::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(NewsStatus::class)],
            'category' => ['nullable', 'integer', 'exists:news_categories,id'],
        ]);

        $articles = NewsArticle::query()
            ->with(['category:id,name', 'author:id,name'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('title', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%'))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('news_category_id', $id))
            // Work in progress first, then by date.
            ->orderByRaw("CASE status WHEN 'review' THEN 0 WHEN 'draft' THEN 1 WHEN 'scheduled' THEN 2 ELSE 3 END")
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.news.index', [
            'articles' => $articles,
            'filters' => $filters,
            'statuses' => NewsStatus::options(),
            'categories' => NewsCategory::query()->ordered()->pluck('name', 'id'),
            'inReview' => NewsArticle::query()->where('status', NewsStatus::Review)->count(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', NewsArticle::class);

        return view('admin.news.form', $this->formData(new NewsArticle(['status' => NewsStatus::Draft])));
    }

    public function store(NewsArticleRequest $request): RedirectResponse
    {
        $article = NewsArticle::create($request->articleData() + ['author_id' => $request->user()->id]);

        return redirect()->route('admin.news.edit', $article)->with('success', "Article \"{$article->title}\" saved as {$article->status->label()}.");
    }

    public function edit(NewsArticle $article): View
    {
        Gate::authorize('update', $article);

        return view('admin.news.form', $this->formData($article->load('author:id,name')));
    }

    public function update(NewsArticleRequest $request, NewsArticle $article): RedirectResponse
    {
        $article->update($request->articleData());

        return redirect()->route('admin.news.edit', $article)->with('success', "Article \"{$article->title}\" saved.");
    }

    public function destroy(NewsArticle $article): RedirectResponse
    {
        Gate::authorize('delete', $article);

        $article->delete();

        return redirect()->route('admin.news.index')->with('success', "Article \"{$article->title}\" deleted.");
    }

    /** @return array<string, mixed> */
    private function formData(NewsArticle $article): array
    {
        $canPublish = auth()->user()->can('publish', NewsArticle::class);

        $statuses = collect(NewsStatus::options())
            ->filter(fn ($label, $value) => $canPublish || ! NewsArticleRequest::statusChangeNeedsPublish($article->exists ? $article : null, $value))
            ->all();

        return [
            'article' => $article,
            'statuses' => $statuses,
            'categories' => NewsCategory::query()->ordered()->pluck('name', 'id')->all(),
            'canPublish' => $canPublish,
        ];
    }
}
