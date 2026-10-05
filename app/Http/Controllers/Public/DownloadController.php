<?php

namespace App\Http\Controllers\Public;

use App\Enums\DownloadCategory;
use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * PRD §18 — public document library. Each download is checked against the
 * document's access level and counted; files are never served directly.
 */
class DownloadController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->validate(['category' => ['nullable', Rule::enum(DownloadCategory::class)]])['category'] ?? null;

        $documents = Document::query()
            ->published()
            ->listableFor($request->user())
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('public.downloads.index', [
            'grouped' => $documents->groupBy(fn (Document $d) => $d->category->label()),
            'categories' => Document::query()->published()->listableFor($request->user())->distinct()->pluck('category')
                ->mapWithKeys(fn (DownloadCategory $c) => [$c->value => $c->label()])->sort(),
            'activeCategory' => $category,
        ]);
    }

    /** GET /downloads/{document:slug} */
    public function download(Request $request, Document $document): StreamedResponse|RedirectResponse
    {
        abort_unless($document->is_published, 404);

        if (! $document->canBeDownloadedBy($request->user())) {
            // Registered-only files send guests to sign in; internal files stay invisible.
            abort_if($request->user() !== null || $document->access_level->value === 'internal', 404);

            return redirect()->guest(route('login'))->with('status', 'Please sign in to download this document.');
        }

        abort_unless(Storage::disk(Document::DISK)->exists($document->file_path), 404);

        $document->increment('download_count');

        return Storage::disk(Document::DISK)->download($document->file_path, $document->original_name);
    }
}
