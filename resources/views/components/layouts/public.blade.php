@props([
    'title' => null,
    'metaDescription' => null,
    // PRD §34 — Open Graph image (site-relative or absolute), page type and
    // an explicit canonical URL when the default would be wrong.
    'ogImage' => null,
    'ogType' => 'website',
    'canonical' => null,
    'noindex' => false,
])

@php
    // Blade components must not use yield() with a default argument —
    // it compiles to echo yieldContent('t', 'default') which is invalid PHP.
    $__defaultTitle = config('moaum.company.name') . ' — ' . config('moaum.company.tagline');
    $__defaultMeta = \App\Models\Setting::get('seo.default_description')
        ?: 'MOAUM Consultancy Services Limited - The official business and investment arm of Rev. Fr. Moses Orshio Adasu University, Makurdi';

    $__title = $title ?? trim($__env->yieldContent('title')) ?: $__defaultTitle;
    $__description = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', (string) ($metaDescription ?? trim($__env->yieldContent('meta_description')) ?: $__defaultMeta))), 300, '…');

    // Canonical: this URL without tracking/sort noise, but keeping pagination
    // and the filters that genuinely change the listing.
    $__query = collect(request()->query())
        ->only(['page', 'category', 'division', 'type', 'tag', 'status', 'mode', 'past'])
        ->filter(fn ($value, $key) => is_scalar($value) && $value !== '' && ! ($key === 'page' && (int) $value <= 1));
    $__canonical = $canonical ?? (url()->current() . ($__query->isNotEmpty() ? '?' . http_build_query($__query->all()) : ''));

    $__image = $ogImage ?: \App\Models\Setting::get('seo.default_image') ?: '/images/hero-home.jpg';
    $__image = str_starts_with($__image, 'http') ? $__image : url($__image);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $__title }}</title>
    <meta name="description" content="{{ $__description }}">
    <link rel="canonical" href="{{ $__canonical }}">
    @if ($noindex)
        <meta name="robots" content="noindex, follow">
    @endif

    {{-- PRD §34 — Open Graph / social cards --}}
    <meta property="og:site_name" content="{{ config('moaum.company.name') }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $__title }}">
    <meta property="og:description" content="{{ $__description }}">
    <meta property="og:url" content="{{ $__canonical }}">
    <meta property="og:image" content="{{ $__image }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $__title }}">
    <meta name="twitter:description" content="{{ $__description }}">
    <meta name="twitter:image" content="{{ $__image }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="alternate" type="application/rss+xml" title="{{ config('moaum.company.short_name') }} News" href="{{ route('news.feed') }}">

    @stack('meta')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-sans text-slate-700 antialiased bg-white min-h-screen flex flex-col">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[100] focus:bg-white focus:text-moaum-red focus:px-4 focus:py-2 focus:rounded-lg">Skip to main content</a>

    @include('partials.navigation')

    <main id="main-content" class="flex-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>
</html>
