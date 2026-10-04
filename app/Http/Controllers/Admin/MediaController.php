<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Admin → Media library (PRD §19/§22, "Media management works" §42).
 *
 * The same endpoints back three clients: the library screen, the media picker
 * on content forms, and image uploads dropped into the Trix editor — the
 * latter two ask for JSON.
 */
class MediaController extends Controller
{
    public const UPLOAD_RULES = ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:max_width=8000,max_height=8000'];

    /** GET /admin/media */
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('viewAny', Media::class);

        $filters = $request->validate(['q' => ['nullable', 'string', 'max:255']]);

        $media = Media::query()
            ->search($filters['q'] ?? null)
            ->latest('id')
            ->paginate($request->wantsJson() ? 24 : 36)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $media->getCollection()->map->toPickerArray(),
                'next' => $media->nextPageUrl(),
            ]);
        }

        return view('admin.media.index', ['media' => $media, 'filters' => $filters]);
    }

    /** POST /admin/media — one or many images. */
    public function store(Request $request, MediaUploader $uploader): RedirectResponse|JsonResponse
    {
        Gate::authorize('create', Media::class);

        $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => self::UPLOAD_RULES,
            'alt_text' => ['nullable', 'string', 'max:255'],
        ], [
            'files.*.max' => 'Each image must be 8 MB or smaller.',
            'files.*.mimes' => 'Upload JPG, PNG or WebP images.',
        ]);

        $created = collect($request->file('files'))
            ->map(fn ($file) => $uploader->store($file, $request->input('alt_text')));

        if ($request->wantsJson()) {
            return response()->json(['data' => $created->map->toPickerArray()], 201);
        }

        return redirect()
            ->route('admin.media.index')
            ->with('success', $created->count() === 1 ? 'Image uploaded.' : "{$created->count()} images uploaded.");
    }

    /** PATCH /admin/media/{media} — alt text and caption. */
    public function update(Request $request, Media $media): RedirectResponse
    {
        Gate::authorize('update', $media);

        $media->update($request->validate([
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]));

        return back()->with('success', 'Image details saved.');
    }

    /** DELETE /admin/media/{media} */
    public function destroy(Media $media): RedirectResponse
    {
        Gate::authorize('delete', $media);

        $media->delete();

        return redirect()->route('admin.media.index')->with('success', 'Image deleted.');
    }
}
