<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LeadershipMember;
use App\Models\Page;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/** PRD §9/§22 — About section, legal pages and custom CMS pages. */
class PageController extends Controller
{
    /** System page routes pass their page key as a route default. */
    public function system(string $key): View
    {
        $page = Page::query()->published()->where('key', $key)->first();

        abort_if($page === null, 404);

        return $this->render($page);
    }

    /** GET /pages/{page:slug} — custom pages only. */
    public function show(Page $page): View
    {
        abort_if($page->isSystem() || ! $page->isPublished(), 404);

        return $this->render($page);
    }

    private function render(Page $page): View
    {
        $inAbout = in_array($page->key, Page::ABOUT_KEYS, true);

        return view('public.pages.show', [
            'page' => $page,
            'aboutNav' => $inAbout ? $this->aboutNav() : [],
            'leaders' => $page->key === 'leadership'
                ? LeadershipMember::query()->published()->ordered()->get()
                : collect(),
        ]);
    }

    /** @return list<array{label: string, url: string, active: bool}> */
    private function aboutNav(): array
    {
        $titles = Page::query()->published()->whereIn('key', Page::ABOUT_KEYS)->pluck('title', 'key');

        return collect(Page::ABOUT_KEYS)
            ->filter(fn ($key) => $titles->has($key))
            ->map(fn ($key) => [
                'label' => $titles[$key],
                'url' => route(Page::SYSTEM[$key][1]),
                'active' => Route::is(Page::SYSTEM[$key][1]),
            ])
            ->values()
            ->all();
    }
}
