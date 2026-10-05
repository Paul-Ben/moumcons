{{-- Admin → Gallery albums (PRD §19). --}}
<x-layouts.admin title="Gallery">
    <div class="space-y-6">
        <x-admin.page-header title="Gallery" description="Photo albums shown on /gallery.">
            @can('create', App\Models\Gallery::class)
                <a href="{{ route('admin.galleries.create') }}" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> New album</a>
            @endcan
        </x-admin.page-header>

        @if ($galleries->isEmpty())
            <div class="card text-center py-16 text-slate-500">
                <x-icon name="image" class="w-10 h-10 mx-auto mb-3 text-slate-300" />
                No albums yet.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($galleries as $gallery)
                    <a href="{{ route('admin.galleries.edit', $gallery) }}" class="card p-0 overflow-hidden group hover:shadow-lg transition">
                        <div class="aspect-[4/3] bg-slate-100">
                            @if ($cover = $gallery->coverUrl())
                                <img src="{{ $cover }}" alt="" class="w-full h-full object-cover" loading="lazy">
                            @endif
                        </div>
                        <div class="p-4">
                            <p class="font-medium text-moaum-charcoal group-hover:text-moaum-blue truncate">{{ $gallery->title }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ $gallery->type->label() }} · {{ $gallery->images_count }} {{ Str::plural('photo', $gallery->images_count) }}</p>
                            <span class="badge mt-2 {{ $gallery->publicationBadgeClasses() }}">{{ $gallery->publicationLabel() }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
            @if ($galleries->hasPages())
                <div>{{ $galleries->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.admin>
