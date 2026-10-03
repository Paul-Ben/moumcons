@props([
    'division' => null,   // App\Models\BusinessDivision (preferred)
    'name' => null,
    'description' => null,
    'status' => 'Active',
    'image' => null,
    'href' => '#',
])

@php
    // Derive props from a division model when provided.
    if ($division) {
        $name ??= $division->name;
        $description ??= $division->short_description;
        $status = $division->status->label();           // Active | Coming Soon | Planned ...
        $image ??= $division->cover_image;
        // Module 5 will register businesses.show; until then link to the directory.
        $href = \Illuminate\Support\Facades\Route::has('businesses.show')
            ? route('businesses.show', $division)
            : route('businesses.index');
    }
    $available = $status === 'Active';
    $badgeColor = match (true) {
        $available => 'green',
        $status === 'Coming Soon' => 'slate',
        default => 'warning',
    };
@endphp

{{-- Division card — Design System §16 & prototype index.html business cards --}}
<a href="{{ $available ? $href : '#' }}" {{ $attributes->merge(['class' => 'group block bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-elevated transition-all duration-300' . ($available ? ' hover:-translate-y-1' : ' opacity-75 pointer-events-auto')]) }}>
    <div class="h-48 overflow-hidden bg-slate-100 relative">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $name }}" loading="lazy"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 {{ $available ? '' : 'grayscale' }}">
            @unless ($available)
                <div class="absolute inset-0 bg-slate-900/20"></div>
            @endunless
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
                <x-icon name="building" class="w-16 h-16 text-slate-300" />
            </div>
        @endif
    </div>
    <div class="p-6">
        <div class="flex items-center justify-between mb-3">
            <x-badge :color="$badgeColor">{{ $status }}</x-badge>
            <x-icon name="chevron-right" class="w-5 h-5 text-slate-400 {{ $available ? 'group-hover:text-moaum-red transition' : '' }}" />
        </div>
        <h3 class="font-display text-xl font-bold text-moaum-charcoal mb-2">{{ $name }}</h3>
        @if ($description)
            <p class="text-slate-600 text-sm mb-4">{{ $description }}</p>
        @endif
        @if ($available)
            <span class="text-moaum-blue font-semibold text-sm inline-flex items-center group-hover:translate-x-1 transition-transform">
                Explore division <x-icon name="arrow-right" class="ml-1 w-4 h-4" />
            </span>
        @else
            <span class="text-slate-500 font-semibold text-sm">Notify when available</span>
        @endif
    </div>
</a>
