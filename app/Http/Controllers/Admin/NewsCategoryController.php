<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin → News categories (PRD §16/§27), managed inline on one screen. */
class NewsCategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', NewsCategory::class);

        return view('admin.news-categories.index', [
            'categories' => NewsCategory::query()->withCount('articles')->ordered()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', NewsCategory::class);

        $category = NewsCategory::create($this->validated($request));

        return back()->with('success', "Category \"{$category->name}\" added.");
    }

    public function update(Request $request, NewsCategory $category): RedirectResponse
    {
        Gate::authorize('update', $category);

        $category->update($this->validated($request, $category));

        return back()->with('success', "Category \"{$category->name}\" saved.");
    }

    public function destroy(NewsCategory $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        $category->delete();

        return back()->with('success', "Category \"{$category->name}\" deleted.");
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?NewsCategory $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('news_categories', 'name')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);
        $data['sort_order'] ??= 0;

        return $data;
    }
}
