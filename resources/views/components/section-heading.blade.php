@props(['eyebrow' => null, 'title', 'description' => null, 'align' => 'center'])

<div {{ $attributes->merge(['class' => ($align === 'center' ? 'text-center ' : '') . 'mb-12 lg:mb-16']) }}>
    @if ($eyebrow)
        <p class="eyebrow mb-2">{{ $eyebrow }}</p>
    @endif
    <h2 class="font-display text-3xl lg:text-4xl font-bold text-moaum-charcoal mb-4">{{ $title }}</h2>
    @if ($description)
        <p class="text-slate-600 max-w-3xl {{ $align === 'center' ? 'mx-auto ' : '' }}text-lg">{{ $description }}</p>
    @endif
</div>
