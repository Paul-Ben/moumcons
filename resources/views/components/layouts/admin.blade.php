<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Reads the `title` attribute passed to <x-layouts.admin title="…">; this
         layout is a component, so @yield('title') would never receive it. --}}
    <title>{{ $title ?? 'Admin' }} &middot; MOAUM Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- admin.js first: it registers Alpine components before app.js starts Alpine. --}}
    @vite(['resources/css/app.css', 'resources/js/admin.js', 'resources/js/app.js'])
    @stack('head')
</head>
{{-- Admin shell — faithful to prototype-docs/admin.html (charcoal sidebar, white topbar) --}}
<body class="font-sans text-slate-700 bg-slate-50 antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-moaum-charcoal text-white transition-transform duration-300 lg:static lg:translate-x-0 flex flex-col">
            <div class="h-16 flex items-center px-5 border-b border-slate-700">
                <x-brand-mark size="sm" subtitle="Admin" :href="route('admin.dashboard')" />
            </div>

            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1" aria-label="Admin navigation">
                {{--
                    Menu comes from App\Support\AdminNav via a view composer in
                    AppServiceProvider: permission-filtered, route-aware, and it
                    flags modules that have not shipped instead of linking to a 404.
                --}}
                @php
                    $currentSection = null;
                @endphp
                @foreach ($adminNav ?? [] as $item)
                    @if (($item['section'] ?? null) !== $currentSection)
                        @php
                            $currentSection = $item['section'];
                        @endphp
                        <p class="px-3 pt-5 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $currentSection }}</p>
                    @endif
                    @include('partials.admin-nav-item', ['item' => $item])
                @endforeach
            </nav>

            <div class="p-4 border-t border-slate-700">
                <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 rounded-lg -m-2 p-2 hover:bg-slate-800 transition" title="My profile">
                    <div class="w-10 h-10 rounded-full bg-slate-600 flex items-center justify-center text-sm font-bold">
                        {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'MA' }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ auth()->user()->name ?? 'Administrator' }}</p>
                        <p class="text-xs text-slate-400">{{ auth()->user()?->getRoleNames()->first() ?? 'Staff' }}</p>
                    </div>
                </a>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-500" aria-label="Toggle sidebar">
                    <x-icon name="menu" class="w-6 h-6" />
                </button>
                <form method="GET" action="{{ route('admin.search') }}" role="search" class="flex-1 max-w-xl mx-6 hidden md:block">
                    <div class="relative">
                        <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                        <label for="admin-search" class="sr-only">Search the admin</label>
                        <input id="admin-search" type="search" name="q" value="{{ request()->routeIs('admin.search') ? request('q') : '' }}"
                               placeholder="Search references, people, or content…" maxlength="100"
                               class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-moaum-blue focus:border-transparent text-sm">
                    </div>
                </form>
                <div class="flex items-center gap-4 ml-auto">
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="text-sm text-slate-500 hover:text-moaum-red font-medium hidden sm:inline">View site →</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-ghost text-sm inline-flex items-center gap-2">
                            <x-icon name="log-out" class="w-4 h-4" /> Sign out
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                {{-- Flash feedback for admin write actions (triage updates, etc).
                     Tone classes are spelled out because Tailwind only generates
                     utilities it can see as complete class names in source. --}}
                @php
                    $flashTones = [
                        'success' => ['border-emerald-200 bg-emerald-50 text-emerald-800', 'check-circle'],
                        'error' => ['border-red-200 bg-red-50 text-red-800', 'x'],
                        'warning' => ['border-amber-200 bg-amber-50 text-amber-800', 'zap'],
                    ];
                @endphp
                @foreach ($flashTones as $key => [$classes, $icon])
                    @if (session($key))
                        <div role="status"
                             class="mb-4 flex items-start gap-2 rounded-xl border px-4 py-3 text-sm {{ $classes }}">
                            <x-icon :name="$icon" class="w-4 h-4 mt-0.5 shrink-0" />
                            <span>{{ session($key) }}</span>
                        </div>
                    @endif
                @endforeach

                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
