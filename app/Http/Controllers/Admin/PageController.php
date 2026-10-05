<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PageStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PageRequest;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Admin → Pages (PRD §9/§22): About section, legal pages and custom pages. */
class PageController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Page::class);

        $pages = Page::query()->orderBy('title')->get();

        return view('admin.pages.index', [
            'systemPages' => $pages->filter->isSystem(),
            'customPages' => $pages->reject->isSystem(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Page::class);

        return view('admin.pages.form', $this->formData(new Page(['status' => PageStatus::Draft])));
    }

    public function store(PageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] ??= PageStatus::Draft->value;

        $page = Page::create($data);

        return redirect()->route('admin.pages.edit', $page)->with('success', "Page \"{$page->title}\" created.");
    }

    public function edit(Page $page): View
    {
        Gate::authorize('update', $page);

        return view('admin.pages.form', $this->formData($page));
    }

    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $page->update($request->validated());

        return redirect()->route('admin.pages.edit', $page)->with('success', "Page \"{$page->title}\" saved.");
    }

    public function destroy(Page $page): RedirectResponse
    {
        Gate::authorize('delete', $page);

        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', "Page \"{$page->title}\" deleted.");
    }

    /** @return array<string, mixed> */
    private function formData(Page $page): array
    {
        return [
            'page' => $page,
            'statuses' => PageStatus::options(),
            'canPublish' => auth()->user()->can('publish', Page::class),
        ];
    }
}
