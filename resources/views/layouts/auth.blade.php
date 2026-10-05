<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MOAUM Admin — Sign in')</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Auth layout — Design System §27: centered card on charcoal backdrop --}}
<body class="font-sans bg-slate-50 antialiased">
    <div class="min-h-screen flex items-center justify-center bg-moaum-charcoal px-4 py-12">
        <div class="w-full max-w-md">

            {{-- Brand --}}
            <div class="flex justify-center mb-8">
                <x-brand-mark :href="route('home')" />
            </div>

            @yield('content')

            <p class="mt-6 text-center text-sm text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-white transition">&larr; Back to website</a>
            </p>
        </div>
    </div>
</body>
</html>
