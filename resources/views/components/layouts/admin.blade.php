<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — MOAUM Admin</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
{{-- Admin shell — faithful to prototype-docs/admin.html (charcoal sidebar, white topbar) --}}
<body class="font-sans text-slate-700 bg-slate-50 antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-moaum-charcoal text-white transition-transform duration-300 lg:static lg:translate-x-0 flex flex-col">
            <div class="h-16 flex items-center px-6 border-b border-slate-700">
                <div class="w-8 h-8 bg-moaum-red rounded flex items-center justify-center font-bold text-sm mr-3">M</div>
                <span class="font-bold text-lg">MOAUM Admin</span>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1" aria-label="Admin navigation">
                {{--
                    Menu comes from App\Support\AdminNav via a view composer in
                    AppServiceProvider: permission-filtered, route-aware, and it
                    flags modules that have not shipped instead of linking to a 404.
                --}}
                @foreach ($adminNav ?? [] as $item)
                    @include('partials.admin-nav-item', ['item' => $item])
                @endforeach
            </nav>

            <div class="p-4 border-t border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-600 flex items-center justify-center text-sm font-bold">
                        {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'MA' }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ auth()->user()->name ?? 'Administrator' }}</p>
                        <p class="text-xs text-slate-400">{{ auth()->user()?->getRoleNames()->first() ?? 'Super Administrator' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-500" aria-label="Toggle sidebar">
                    <x-icon name="menu" class="w-6 h-6" />
                </button>
                <div class="flex-1 max-w-xl mx-6 hidden md:block">
                    <div class="relative">
                        <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                        <input type="search" placeholder="Search enquiries, services, or users..."
                               class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-moaum-blue focus:border-transparent text-sm">
                    </div>
                </div>
                <div class="flex items-center gap-4 ml-auto">
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-ghost text-sm inline-flex items-center gap-2">
                            <x-icon name="log-out" class="w-4 h-4" /> Sign out
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
