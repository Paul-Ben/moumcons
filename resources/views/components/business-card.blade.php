@props([
    'name' => null,
    'description' => null,
    'status' => 'Active',
    'image' => null,
    'href' => '#',
])

{{-- Division card — Design System §16 & prototype index.html business cards --}}
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'group block bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-elevated transition-all duration-300 hover:-translate-y-1']) }}>
    <div class="h-48 overflow-hidden bg-slate-100">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $name }}" loading="lazy"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
                <x-icon name="building" class="w-16 h-16 text-slate-300" />
            </div>
        @endif
    </div>
    <div class="p-6">
        <div class="flex items-center justify-between mb-3">
            <x-badge :color="$status === 'Active' ? 'green' : 'slate'">{{ $status }}</x-badge>
            <x-icon name="chevron-right" class="w-5 h-5 text-slate-400 group-hover:text-moaum-red transition" />
        </div>
        <h3 class="font-display text-xl font-bold text-moaum-charcoal mb-2">{{ $name }}</h3>
        @if ($description)
            <p class="text-slate-600 text-sm mb-4">{{ $description }}</p>
        @endif
        <span class="text-moaum-blue font-semibold text-sm inline-flex items-center group-hover:translate-x-1 transition-transform">
            Explore division <x-icon name="arrow-right" class="ml-1 w-4 h-4" />
        </span>
    </div>
</a>
