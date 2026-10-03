@props(['title' => null, 'metaDescription' => null])

@php
    // Blade components must not use yield() with a default argument —
    // it compiles to echo yieldContent('t', 'default') which is invalid PHP.
    $__defaultTitle = config('moaum.company.name') . ' — ' . config('moaum.company.tagline');
    $__defaultMeta = 'MOAUM Consultancy Services Limited - The official business and investment arm of Rev. Fr. Moses Orshio Adasu University, Makurdi';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? trim($__env->yieldContent('title')) ?: $__defaultTitle }}</title>
    <meta name="description" content="{{ $metaDescription ?? trim($__env->yieldContent('meta_description')) ?: $__defaultMeta }}">

    {{-- Canonical/OG basics (full SEO meta handled in Module 11) --}}
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
