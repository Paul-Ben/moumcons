{{-- Admin → Media library (PRD §19/§22). --}}
<x-layouts.admin title="Media Library">
    <div class="space-y-6">
        <x-admin.page-header title="Media Library"
            description="Images are resized and converted to WebP on upload. Pick them from any content form." />

        @can('create', App\Models\Media::class)
            <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="card">
                @csrf
                <div class="grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
                    <div>
                        <label for="files" class="form-label">Upload images</label>
                        <input id="files" type="file" name="files[]" multiple required accept="image/jpeg,image/png,image/webp"
                               class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-moaum-blue/10 file:px-4 file:py-2 file:font-semibold file:text-moaum-blue hover:file:bg-moaum-blue/20">
                        <p class="mt-1 text-xs text-slate-400">JPG, PNG or WebP, up to 8 MB each, 20 at a time.</p>
                        @error('files') <p class="form-error">{{ $message }}</p> @enderror
                        @error('files.*') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <x-admin.input name="alt_text" label="Alt text (optional)" hint="Describes the image for screen readers. Applied to every file in this upload." />
                    <button type="submit" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> Upload</button>
                </div>
            </form>
        @endcan

        <form method="GET" action="{{ route('admin.media.index') }}" class="flex gap-2">
            <label for="q" class="sr-only">Search</label>
            <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by file name, alt text or caption…" class="form-input text-sm max-w-md">
            <button type="submit" class="btn-ghost text-sm border border-slate-200">Search</button>
        </form>

        @if ($media->isEmpty())
            <div class="card text-center py-16 text-slate-500">
                <x-icon name="image" class="w-10 h-10 mx-auto mb-3 text-slate-300" />
                No images {{ filled($filters['q'] ?? null) ? 'match that search' : 'uploaded yet' }}.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($media as $item)
                    <div class="card p-0 overflow-hidden flex flex-col" x-data="{ editing: false }">
                        <a href="{{ $item->url() }}" target="_blank" rel="noopener" class="block aspect-[4/3] bg-slate-100">
                            <img src="{{ $item->thumbUrl() }}" alt="{{ $item->alt_text }}" loading="lazy" class="w-full h-full object-cover">
                        </a>
                        <div class="p-4 flex-1 flex flex-col gap-2 text-sm">
                            <p class="font-medium text-moaum-charcoal truncate" title="{{ $item->original_name }}">{{ $item->original_name }}</p>
                            <p class="text-xs text-slate-400">{{ $item->width }}×{{ $item->height }} &middot; {{ $item->humanSize() }} &middot; {{ $item->created_at->format('d M Y') }}</p>
                            @if ($item->alt_text)
                                <p class="text-xs text-slate-500 line-clamp-2">Alt: {{ $item->alt_text }}</p>
                            @else
                                <p class="text-xs text-warning">No alt text</p>
                            @endif

                            <div class="flex items-center gap-1 mt-auto pt-2">
                                <button type="button" class="btn-ghost text-xs px-2 py-1"
                                        x-data @click="navigator.clipboard.writeText(@js($item->url())); $el.textContent = 'Copied'">Copy URL</button>
                                @can('update', $item)
                                    <button type="button" class="btn-ghost text-xs px-2 py-1" @click="editing = !editing">Details</button>
                                @endcan
                                @can('delete', $item)
                                    <x-admin.delete-button :action="route('admin.media.destroy', $item)" label=""
                                        confirm="Delete this image? Pages using it will show no image." class="ml-auto" />
                                @endcan
                            </div>

                            @can('update', $item)
                                <form x-show="editing" x-cloak method="POST" action="{{ route('admin.media.update', $item) }}" class="space-y-2 pt-2 border-t border-slate-100">
                                    @csrf
                                    @method('PATCH')
                                    <input name="alt_text" value="{{ $item->alt_text }}" placeholder="Alt text" maxlength="255" class="form-input h-9 text-xs" aria-label="Alt text">
                                    <input name="caption" value="{{ $item->caption }}" placeholder="Caption" maxlength="255" class="form-input h-9 text-xs" aria-label="Caption">
                                    <button type="submit" class="btn-secondary text-xs px-3 py-1.5">Save</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($media->hasPages())
                <div>{{ $media->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.admin>
