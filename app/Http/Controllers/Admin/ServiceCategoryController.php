<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin → Service categories (PRD §11/§27). A short list managed on one
 * screen: add, rename/reorder inline, delete (services become uncategorised).
 */
class ServiceCategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', ServiceCategory::class);

        return view('admin.service-categories.index', [
            'categories' => ServiceCategory::query()->withCount('services')->ordered()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', ServiceCategory::class);

        $category = ServiceCategory::create($this->validated($request));

        return back()->with('success', "Category \"{$category->name}\" added.");
    }

    public function update(Request $request, ServiceCategory $category): RedirectResponse
    {
        Gate::authorize('update', $category);

        $category->update($this->validated($request, $category));

        return back()->with('success', "Category \"{$category->name}\" saved.");
    }

    public function destroy(ServiceCategory $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        $category->delete();

        return back()->with('success', "Category \"{$category->name}\" deleted.");
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?ServiceCategory $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('service_categories', 'name')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);
        $data['sort_order'] ??= 0;

        return $data;
    }
}
