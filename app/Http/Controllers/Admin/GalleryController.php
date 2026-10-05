<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GalleryType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Concerns\ContentRules;
use App\Http\Requests\Concerns\HandlesPublication;
use App\Models\BusinessDivision;
use App\Models\Gallery;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin → Gallery albums (PRD §19). Photos come from the media library. */
class GalleryController extends Controller
{
    use ContentRules, HandlesPublication;

    public function index(): View
    {
        Gate::authorize('viewAny', Gallery::class);

        return view('admin.galleries.index', [
            'galleries' => Gallery::query()->with(['division:id,name', 'images'])->withCount('images')->ordered()->paginate(24),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Gallery::class);

        return view('admin.galleries.form', $this->formData(new Gallery(['type' => GalleryType::Corporate])));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Gallery::class);

        $gallery = DB::transaction(function () use ($request) {
            [$data, $images] = $this->validated($request, null);
            $gallery = Gallery::create($data);
            $this->syncImages($gallery, $images);

            return $gallery;
        });

        return redirect()->route('admin.galleries.edit', $gallery)->with('success', "Album \"{$gallery->title}\" created.");
    }

    public function edit(Gallery $gallery): View
    {
        Gate::authorize('update', $gallery);

        return view('admin.galleries.form', $this->formData($gallery->load('images')));
    }

    public function update(Request $request, Gallery $gallery): RedirectResponse
    {
        Gate::authorize('update', $gallery);

        DB::transaction(function () use ($request, $gallery) {
            [$data, $images] = $this->validated($request, $gallery);
            $gallery->update($data);
            $this->syncImages($gallery, $images);
        });

        return redirect()->route('admin.galleries.edit', $gallery)->with('success', "Album \"{$gallery->title}\" saved.");
    }

    public function destroy(Gallery $gallery): RedirectResponse
    {
        Gate::authorize('delete', $gallery);

        $gallery->delete();

        return redirect()->route('admin.galleries.index')->with('success', "Album \"{$gallery->title}\" deleted.");
    }

    /** @return array{0: array<string, mixed>, 1: list<array{image: string, caption: ?string}>} */
    private function validated(Request $request, ?Gallery $gallery): array
    {
        if ($request->hasAny(['publish', 'published_at'])) {
            Gate::authorize('publish', Gallery::class);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => $this->slugRules('galleries', $gallery?->id),
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', Rule::enum(GalleryType::class)],
            'business_division_id' => ['nullable', 'integer', 'exists:business_divisions,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'cover_image' => $this->imageRules(),
            'event_date' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            ...$this->publicationRules(),
            'gallery' => ['nullable', 'array', 'max:200'],
            'gallery.*.image' => ['required', ...array_slice($this->imageRules(), 1)],
            'gallery.*.caption' => ['nullable', 'string', 'max:255'],
        ], $this->contentMessages());

        $images = array_values(array_map(
            fn ($row) => ['image' => $row['image'], 'caption' => $row['caption'] ?? null],
            $data['gallery'] ?? []
        ));
        unset($data['gallery']);
        $data['sort_order'] ??= 0;

        return [$this->applyPublication($data, $gallery), $images];
    }

    /** @param  list<array{image: string, caption: ?string}>  $images */
    private function syncImages(Gallery $gallery, array $images): void
    {
        $gallery->images()->delete();

        foreach ($images as $position => $image) {
            $gallery->images()->create($image + ['sort_order' => $position + 1]);
        }
    }

    /** @return array<string, mixed> */
    private function formData(Gallery $gallery): array
    {
        return [
            'gallery' => $gallery,
            'types' => GalleryType::options(),
            'divisions' => BusinessDivision::query()->ordered()->pluck('name', 'id')->all(),
            'projects' => Project::query()->orderBy('title')->pluck('title', 'id')->all(),
            'canPublish' => auth()->user()->can('publish', Gallery::class),
        ];
    }
}
