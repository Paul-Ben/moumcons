{{-- Admin → upload / edit a document (PRD §18). --}}
@php($editing = $document->exists)

<x-layouts.admin :title="$editing ? 'Edit '.$document->title : 'Upload document'">
    <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.documents.update', $document) : route('admin.documents.store') }}" class="space-y-6 max-w-4xl">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? $document->title : 'Upload a document'" :back="route('admin.documents.index')" back-label="All downloads" />

        <div class="card space-y-4">
            <x-admin.input name="title" label="Title" :value="$document->title" required maxlength="255" />
            <x-admin.textarea name="description" label="Description" :value="$document->description" rows="2" maxlength="500" />

            <div>
                <label for="file" class="form-label">{{ $editing ? 'Replace file' : 'File' }} @unless ($editing)<span class="text-moaum-red" aria-hidden="true">*</span>@endunless</label>
                @if ($editing)
                    <p class="text-sm text-slate-600 mb-2">
                        Current: <a href="{{ route('admin.documents.file', $document) }}" class="text-moaum-blue hover:underline">{{ $document->original_name }}</a>
                        ({{ $document->humanSize() }}, downloaded {{ number_format($document->download_count) }} {{ Str::plural('time', $document->download_count) }})
                    </p>
                @endif
                <input id="file" type="file" name="file" accept="{{ $accept }}" @required(! $editing)
                       class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-moaum-blue/10 file:px-4 file:py-2 file:font-semibold file:text-moaum-blue hover:file:bg-moaum-blue/20">
                <p class="mt-1 text-xs text-slate-400">PDF, Word, Excel, PowerPoint, CSV, text, ZIP or image — up to 25 MB.</p>
                @error('file') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="card space-y-4">
            <div class="grid sm:grid-cols-2 gap-4">
                <x-admin.select name="category" label="Category" :options="$categories" :value="$document->category" required />
                <x-admin.select name="access_level" label="Who can download" :options="$accessLevels" :value="$document->access_level" required
                                hint="Registered = signed-in users. Internal = staff with download access." />
                <x-admin.select name="business_division_id" label="Division" :options="$divisions" :value="$document->business_division_id" placeholder="— None —" />
                <x-admin.input name="sort_order" type="number" min="0" label="Display order" :value="$document->sort_order ?? 0" />
            </div>
            @if ($canPublish)
                <x-admin.checkbox name="is_published" label="List on the public Downloads page" :checked="$document->is_published" />
            @else
                <p class="text-xs text-slate-400">Publishing needs the publish-downloads permission.</p>
            @endif
        </div>

        <div class="card">
            <x-admin.form-actions :cancel="route('admin.documents.index')" :submit="$editing ? 'Save' : 'Upload'" />
        </div>
    </form>

    @if ($editing)
        @can('delete', $document)
            <div class="card mt-6 max-w-4xl flex justify-end">
                <x-admin.delete-button :action="route('admin.documents.destroy', $document)" label="Delete document" confirm="Delete this document and its file?" />
            </div>
        @endcan
    @endif
</x-layouts.admin>
