<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccessLevel;
use App\Enums\DownloadCategory;
use App\Http\Controllers\Controller;
use App\Models\BusinessDivision;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Admin → Downloads / document library (PRD §18). */
class DocumentController extends Controller
{
    /** PRD §32 secure file validation: office documents, PDFs, images and archives only. */
    private const EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt', 'zip', 'jpg', 'jpeg', 'png'];

    private const MAX_KB = 25600; // 25 MB

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Document::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', Rule::enum(DownloadCategory::class)],
        ]);

        $documents = Document::query()
            ->with('division:id,name')
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('title', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%'))
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(30)
            ->withQueryString();

        return view('admin.documents.index', [
            'documents' => $documents,
            'filters' => $filters,
            'categories' => DownloadCategory::options(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Document::class);

        return view('admin.documents.form', $this->formData(new Document([
            'access_level' => AccessLevel::Public,
            'category' => DownloadCategory::Brochures,
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Document::class);
        $this->authorizePublishing($request);

        $data = $this->validated($request, null);
        $data += $this->storeFile($request->file('file'));
        $data['is_published'] ??= false;

        $document = Document::create($data);

        return redirect()->route('admin.documents.edit', $document)->with('success', "\"{$document->title}\" uploaded.");
    }

    public function edit(Document $document): View
    {
        Gate::authorize('update', $document);

        return view('admin.documents.form', $this->formData($document));
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        Gate::authorize('update', $document);
        $this->authorizePublishing($request);

        $data = $this->validated($request, $document);

        if ($request->hasFile('file')) {
            $old = $document->file_path;
            $data += $this->storeFile($request->file('file'));
            $document->update($data);
            Storage::disk(Document::DISK)->delete($old);
        } else {
            $document->update($data);
        }

        return redirect()->route('admin.documents.edit', $document)->with('success', "\"{$document->title}\" saved.");
    }

    public function destroy(Document $document): RedirectResponse
    {
        Gate::authorize('delete', $document);

        $document->delete();

        return redirect()->route('admin.documents.index')->with('success', "\"{$document->title}\" deleted.");
    }

    /** GET /admin/documents/{document}/file — staff preview, not counted. */
    public function file(Document $document): StreamedResponse
    {
        Gate::authorize('view', $document);

        abort_unless(Storage::disk(Document::DISK)->exists($document->file_path), 404);

        return Storage::disk(Document::DISK)->download($document->file_path, $document->original_name);
    }

    private function authorizePublishing(Request $request): void
    {
        if ($request->has('is_published')) {
            Gate::authorize('publish', Document::class);
        }
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Document $document): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'category' => ['required', Rule::enum(DownloadCategory::class)],
            'access_level' => ['required', Rule::enum(AccessLevel::class)],
            'business_division_id' => ['nullable', 'integer', 'exists:business_divisions,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_published' => ['sometimes', 'boolean'],
            'file' => [$document ? 'nullable' : 'required', 'file', 'mimes:'.implode(',', self::EXTENSIONS), 'max:'.self::MAX_KB],
        ], [
            'file.mimes' => 'Upload a PDF, Office document, CSV, text, ZIP or image file.',
            'file.max' => 'Files must be 25 MB or smaller.',
        ]);

        unset($data['file']);
        $data['sort_order'] ??= 0;

        return $data;
    }

    /** @return array{file_path: string, original_name: string, mime_type: string, file_size: int} */
    private function storeFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $extension = in_array($extension, self::EXTENSIONS, true) ? $extension : 'bin';
        $base = Str::limit(Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'document', 80, '');

        return [
            'file_path' => $file->storeAs('library/'.now()->format('Y'), $base.'-'.Str::lower(Str::random(8)).'.'.$extension, Document::DISK),
            'original_name' => Str::limit($base, 80, '').'.'.$extension,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'file_size' => $file->getSize(),
        ];
    }

    /** @return array<string, mixed> */
    private function formData(Document $document): array
    {
        return [
            'document' => $document,
            'categories' => DownloadCategory::options(),
            'accessLevels' => AccessLevel::options(),
            'divisions' => BusinessDivision::query()->ordered()->pluck('name', 'id')->all(),
            'canPublish' => auth()->user()->can('publish', Document::class),
            'accept' => '.'.implode(',.', self::EXTENSIONS),
        ];
    }
}
