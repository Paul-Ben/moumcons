{{-- Centered container — Design System §9 (max-width 1280px, responsive padding) --}}
<div {{ $attributes->merge(['class' => 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8']) }}>
    {{ $slot }}
</div>
