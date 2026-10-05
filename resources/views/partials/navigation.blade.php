{{-- Site header — prototype-docs/index.html + Design System §18/§19: sticky white
     header, logo, main navigation, primary CTA; About dropdown and Our Businesses
     mega menu on desktop (xl+), accordion slide-down menu below that. --}}
@php
    $nav = config('moaum.nav.primary');
    $groups = config('moaum.nav.business_groups');
    $divisionsByCategory = \App\Support\PublicNavigation::divisions()->groupBy('category');
    $divisionCount = $divisionsByCategory->flatten()->count();

    // Literal class names so Tailwind can see them (no string-built classes).
    $accentBar = ['red' => 'bg-moaum-red', 'blue' => 'bg-moaum-blue', 'green' => 'bg-moaum-green', 'charcoal' => 'bg-moaum-charcoal'];
    $accentText = ['red' => 'text-moaum-red', 'blue' => 'text-moaum-blue', 'green' => 'text-moaum-green', 'charcoal' => 'text-moaum-charcoal'];

    // Which top-level item is "current": its route family, its children, or the businesses section.
    $isActive = function (array $item): bool {
        if (! empty($item['mega'])) {
            return request()->routeIs('businesses.*');
        }
        if (! empty($item['children'])) {
            return request()->routeIs(collect($item['children'])->pluck('route')->all());
        }
        $route = $item['route'];

        return $route === 'home' ? request()->routeIs('home') : request()->routeIs(\Illuminate\Support\Str::before($route, '.').'.*');
    };

    $linkBase = 'relative inline-flex items-center gap-1 whitespace-nowrap px-2.5 py-2 rounded-lg text-[15px] font-medium transition';
    $linkIdle = 'text-slate-700 hover:text-moaum-red hover:bg-slate-50';
    $linkActive = 'text-moaum-red after:absolute after:left-2.5 after:right-2.5 after:-bottom-[19px] after:h-0.5 after:rounded-full after:bg-moaum-red';
@endphp

<header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-200 transition-shadow"
        x-data="{ mobileMenu: false, scrolled: false }"
        x-init="scrolled = window.scrollY > 8"
        @scroll.window.passive="scrolled = window.scrollY > 8"
        :class="scrolled ? 'shadow-md' : 'shadow-sm'">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 h-20">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center shrink-0 pr-2" aria-label="{{ config('moaum.company.name') }} — Home">
                <img src="{{ asset('images/logo.jpg') }}" alt="" class="h-14 w-auto">
            </a>

            {{-- Desktop navigation --}}
            <nav class="hidden xl:flex items-center gap-0.5" aria-label="Main navigation">
                @foreach ($nav as $item)
                    @php
                        $active = $isActive($item);
                    @endphp
                    @if (! empty($item['mega']))
                        {{-- Our Businesses mega menu (Design System §19). The wrapper is
                             static so the panel aligns to the header container, not the link. --}}
                        <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false"
                             @keydown.escape.window="open = false" @focusout="if (! $el.contains($event.relatedTarget)) open = false">
                            <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="true"
                                    class="{{ $linkBase }} {{ $active ? $linkActive : $linkIdle }}" :class="open && 'text-moaum-red bg-slate-50'">
                                {{ $item['label'] }}
                                <x-icon name="chevron-down" class="h-4 w-4 transition-transform" />
                            </button>

                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                 class="absolute left-4 right-4 sm:left-6 sm:right-6 lg:left-8 lg:right-8 top-full pt-3">
                                <div class="bg-white rounded-2xl shadow-elevated border border-slate-200 overflow-hidden">
                                    <div class="grid grid-cols-5 gap-6 p-8">
                                        @foreach ($groups as $group)
                                            <div>
                                                <p class="flex items-center gap-2 mb-4 whitespace-nowrap text-[11px] font-bold uppercase tracking-wide {{ $accentText[$group['accent']] ?? 'text-moaum-charcoal' }}">
                                                    <span class="h-4 w-1 rounded-full {{ $accentBar[$group['accent']] ?? 'bg-moaum-charcoal' }}" aria-hidden="true"></span>
                                                    {{ $group['heading'] }}
                                                </p>
                                                <ul class="space-y-1">
                                                    @forelse (($divisionsByCategory[$group['heading']] ?? collect())->take(6) as $business)
                                                        <li>
                                                            <a href="{{ route('businesses.show', $business) }}"
                                                               class="group/item flex items-start justify-between gap-2 -mx-2 px-2 py-1.5 rounded-md text-sm text-slate-600 hover:bg-slate-50 hover:text-moaum-blue transition">
                                                                <span>{{ $business->name }}</span>
                                                                @if ($business->status !== \App\Enums\DivisionStatus::Active)
                                                                    <span class="shrink-0 mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Soon</span>
                                                                @endif
                                                            </a>
                                                        </li>
                                                    @empty
                                                        <li class="text-sm text-slate-400">Coming soon</li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-8 py-4 bg-slate-50 border-t border-slate-200">
                                        <p class="text-sm text-slate-600">
                                            <span class="font-semibold text-moaum-charcoal">{{ $divisionCount }} business divisions</span>
                                            under one university-backed enterprise.
                                        </p>
                                        <a href="{{ route('businesses.index') }}" class="inline-flex items-center gap-1.5 whitespace-nowrap text-sm font-semibold text-moaum-red hover:gap-2.5 transition-all">
                                            View all businesses <x-icon name="arrow-right" class="w-4 h-4" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif (! empty($item['children']))
                        {{-- About dropdown --}}
                        <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false"
                             @keydown.escape.window="open = false" @focusout="if (! $el.contains($event.relatedTarget)) open = false">
                            <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="true"
                                    class="{{ $linkBase }} {{ $active ? $linkActive : $linkIdle }}" :class="open && 'text-moaum-red bg-slate-50'">
                                {{ $item['label'] }}
                                <x-icon name="chevron-down" class="h-4 w-4" />
                            </button>
                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                 class="absolute left-0 top-full pt-3 w-64">
                                <div class="bg-white rounded-xl shadow-elevated border border-slate-200 p-2">
                                    @foreach ($item['children'] as $child)
                                        <a href="{{ route($child['route']) }}"
                                           @class(['block px-3 py-2.5 rounded-lg text-sm transition',
                                                   'bg-moaum-red/5 text-moaum-red font-semibold' => request()->routeIs($child['route']),
                                                   'text-slate-700 hover:bg-slate-50 hover:text-moaum-red' => ! request()->routeIs($child['route'])])>
                                            {{ $child['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route($item['route']) }}" class="{{ $linkBase }} {{ $active ? $linkActive : $linkIdle }}" @if ($active) aria-current="page" @endif>
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            {{-- Desktop utilities + CTAs --}}
            <div class="hidden xl:flex items-center gap-1.5 shrink-0 pl-3 border-l border-slate-200">
                <a href="{{ route('search') }}" class="p-2 rounded-lg text-slate-500 hover:text-moaum-red hover:bg-slate-50 transition" aria-label="Search the site">
                    <x-icon name="search" class="w-5 h-5" />
                </a>
                <a href="{{ route('requests.quote.create') }}" class="whitespace-nowrap px-3 py-2 rounded-lg text-sm font-semibold text-moaum-blue hover:bg-moaum-blue/5 transition">
                    Request a Quote
                </a>
                <a href="{{ route('requests.service.create') }}" class="btn-primary whitespace-nowrap !px-5 !py-2.5 text-sm">
                    Request a Service
                </a>
            </div>

            {{-- Mobile / tablet: search + menu trigger --}}
            <div class="flex items-center gap-1 xl:hidden">
                <a href="{{ route('search') }}" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100" aria-label="Search the site">
                    <x-icon name="search" class="h-6 w-6" />
                </a>
                <button type="button" @click="mobileMenu = !mobileMenu" class="p-2 rounded-lg text-slate-700 hover:bg-slate-100"
                        aria-label="Toggle menu" aria-controls="mobile-menu" :aria-expanded="mobileMenu.toString()">
                    <span x-show="!mobileMenu"><x-icon name="menu" class="h-6 w-6" /></span>
                    <span x-show="mobileMenu" x-cloak><x-icon name="x" class="h-6 w-6" /></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile / tablet menu --}}
    <div id="mobile-menu" x-show="mobileMenu" x-cloak x-transition.opacity @keydown.escape.window="mobileMenu = false"
         class="xl:hidden border-t border-slate-200 bg-white">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 pt-3 pb-6 max-h-[calc(100vh-5rem)] overflow-y-auto" aria-label="Mobile navigation">
            <ul class="space-y-1">
                @foreach ($nav as $item)
                    @php
                        $active = $isActive($item);
                    @endphp
                    @if (! empty($item['mega']) || ! empty($item['children']))
                        <li x-data="{ open: {{ $active ? 'true' : 'false' }} }">
                            <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                                    @class(['w-full flex items-center justify-between px-3 py-3 rounded-lg text-base font-medium transition',
                                            'text-moaum-red bg-moaum-red/5' => $active, 'text-slate-700 hover:bg-slate-50' => ! $active])>
                                {{ $item['label'] }}
                                <span class="transition-transform" :class="open && 'rotate-180'"><x-icon name="chevron-down" class="h-5 w-5" /></span>
                            </button>
                            <div x-show="open" x-cloak class="mt-1 mb-2 ml-3 pl-3 border-l-2 border-slate-100 space-y-0.5">
                                @if (! empty($item['mega']))
                                    @foreach ($groups as $group)
                                        @php
                                            $members = $divisionsByCategory[$group['heading']] ?? collect();
                                        @endphp
                                        @continue($members->isEmpty())
                                        <p class="px-3 pt-3 pb-1 text-[11px] font-bold uppercase tracking-wider {{ $accentText[$group['accent']] ?? 'text-moaum-charcoal' }}">{{ $group['heading'] }}</p>
                                        @foreach ($members as $business)
                                            <a href="{{ route('businesses.show', $business) }}" class="block px-3 py-2 rounded-md text-sm text-slate-600 hover:bg-slate-50 hover:text-moaum-blue">{{ $business->name }}</a>
                                        @endforeach
                                    @endforeach
                                    <a href="{{ route('businesses.index') }}" class="flex items-center gap-1.5 px-3 py-2.5 mt-1 text-sm font-semibold text-moaum-red">
                                        View all businesses <x-icon name="arrow-right" class="w-4 h-4" />
                                    </a>
                                @else
                                    @foreach ($item['children'] as $child)
                                        <a href="{{ route($child['route']) }}"
                                           @class(['block px-3 py-2 rounded-md text-sm', 'text-moaum-red font-semibold' => request()->routeIs($child['route']),
                                                   'text-slate-600 hover:bg-slate-50 hover:text-moaum-red' => ! request()->routeIs($child['route'])])>{{ $child['label'] }}</a>
                                    @endforeach
                                @endif
                            </div>
                        </li>
                    @else
                        <li>
                            <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                               @class(['block px-3 py-3 rounded-lg text-base font-medium transition',
                                       'text-moaum-red bg-moaum-red/5' => $active, 'text-slate-700 hover:bg-slate-50' => ! $active])>{{ $item['label'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>

            <form method="GET" action="{{ route('search') }}" class="mt-4" role="search">
                <label for="mobile-search" class="sr-only">Search the site</label>
                <input id="mobile-search" type="search" name="q" placeholder="Search the site…" class="form-input">
            </form>
            <div class="mt-4 grid sm:grid-cols-2 gap-3">
                <a href="{{ route('requests.service.create') }}" class="btn-primary w-full">Request a Service</a>
                <a href="{{ route('requests.quote.create') }}" class="btn-outline w-full">Request a Quote</a>
            </div>
        </nav>
    </div>
</header>
