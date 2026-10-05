{{--
    Branded error shell (PRD §41 graceful error handling). Deliberately
    standalone — no navigation, footer or database reads — so it still renders
    when the error is the database itself.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('title') · {{ config('moaum.company.short_name', 'MOAUM') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
</head>
<body class="font-sans text-slate-700 antialiased bg-slate-50 min-h-screen flex items-center justify-center p-6">
    <main class="max-w-lg w-full text-center">
        <a href="{{ url('/') }}" class="inline-block mb-10">
            <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('moaum.company.name', 'MOAUM Consultancy Services Limited') }}" class="h-16 w-auto mx-auto">
        </a>
        <p class="font-display text-7xl font-extrabold text-moaum-red/20 leading-none mb-4" aria-hidden="true">@yield('code')</p>
        <h1 class="font-display text-3xl font-bold text-moaum-charcoal mb-3">@yield('title')</h1>
        <p class="text-slate-600 mb-8">@yield('message')</p>
        <div class="flex flex-wrap justify-center gap-3">
            @section('actions')
                <a href="{{ url('/') }}" class="btn-primary">Back to the home page</a>
                <a href="{{ url('/contact') }}" class="btn-outline">Contact us</a>
            @show
        </div>
    </main>
</body>
</html>
