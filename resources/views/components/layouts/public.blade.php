@props(['title' => null, 'metaDescription' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? yield('title', config('moaum.company.name') . ' — ' . config('moaum.company.tagline')) }}</title>
    <meta name="description" content="{{ $metaDescription ?? yield('meta_description', 'MOAUM Consultancy Services Limited - The official business and investment arm of Rev. Fr. Moses Orshio Adasu University, Makurdi') }}">

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
