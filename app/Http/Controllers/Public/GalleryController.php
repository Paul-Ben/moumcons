<?php

namespace App\Http\Controllers\Public;

use App\Enums\GalleryType;
use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** PRD §19 — public photo albums (/gallery, /gallery/{slug}). */
class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->validate(['type' => ['nullable', Rule::enum(GalleryType::class)]])['type'] ?? null;

        $galleries = Gallery::query()
            ->published()
            ->with(['images' => fn ($q) => $q->limit(1)])
            ->withCount('images')
            ->whereHas('images')
            ->when($type, fn ($q) => $q->where('type', $type))
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('public.gallery.index', [
            'galleries' => $galleries,
            'types' => GalleryType::options(),
            'activeType' => $type,
        ]);
    }

    public function show(Gallery $gallery): View
    {
        abort_unless($gallery->isPublished(), 404);

        return view('public.gallery.show', ['gallery' => $gallery->load(['images', 'division', 'project'])]);
    }
}
