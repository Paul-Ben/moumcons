{{-- Site header — faithful to prototype-docs/index.html (sticky white header,
     desktop nav + About dropdown + Businesses mega menu, mobile slide-down) --}}
@php
    $nav = config('moaum.nav.primary');
    $groups = config('moaum.nav.business_groups');
    // Mega menu + footer division links come from the database (Module 2).
    $divisionsByCategory = \App\Models\BusinessDivision::query()
        ->publiclyVisible()->ordered()->get()->groupBy('category');
@endphp

<header class="sticky top-0 z-50 bg-white shadow-sm border-b border-slate-200" x-data="{ mobileMenu: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">

            {{-- Logo --}}
            <div class="flex-shrink-0 flex items-center">
                <a href="{{ route('home') }}" aria-label="{{ config('moaum.company.name') }} — Home">
                    <img src="{{ asset('images/logo.jpg') }}" alt="{{ config('moaum.company.name') }}" class="h-16 w-auto">
                </a>
            </div>

            {{-- Desktop navigation --}}
            <nav class="hidden lg:flex space-x-8" aria-label="Main navigation">
                @foreach ($nav as $item)
                    @if (($item['mega'] ?? false))
                        {{-- Mega menu: Our Businesses (Design System §19) --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @mouseenter="open = true" @mouseleave="open = false" @click="open = !open"
                                    class="text-slate-700 hover:text-moaum-red font-medium transition flex items-center"
                                    aria-haspopup="true">
                                {{ $item['label'] }} <x-icon name="chevron-down" class="ml-1 h-4 w-4" />
                            </button>
                            <div x-show="open" @mouseenter="open = true" @mouseleave="open = false" x-transition x-cloak
                                 class="absolute top-full left-0 mt-2 w-[900px] bg-white rounded-lg shadow-elevated border border-slate-200 p-6">
                                <div class="grid grid-cols-5 gap-6">
                                    @foreach ($groups as $group)
                                        <div>
                                            <h4 class="font-semibold text-moaum-{{ $group['accent'] }} mb-3 text-sm uppercase tracking-wide">{{ $group['heading'] }}</h4>
                                            <ul class="space-y-2 text-sm">
                                                @foreach (($divisionsByCategory[$group['heading']] ?? collect())->take(6) as $business)
                                                    <li><a href="{{ route('businesses.index', ['division' => $business->slug]) }}" class="text-slate-600 hover:text-moaum-blue">{{ $business->name }}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                    <div class="pt-2">
                                        <a href="{{ route('businesses.index') }}" class="text-moaum-red font-medium inline-flex items-center">
                                            <x-icon name="arrow-right" class="mr-1 w-4 h-4" /> View All Businesses
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif (($item['children'] ?? null))
                        {{-- Simple dropdown (About) --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false"
                                    class="text-slate-700 hover:text-moaum-red font-medium transition flex items-center"
                                    aria-haspopup="true">
                                {{ $item['label'] }} <x-icon name="chevron-down" class="ml-1 h-4 w-4" />
                            </button>
                            <div x-show="open" x-transition x-cloak
                                 class="absolute top-full left-0 mt-2 w-56 bg-white rounded-lg shadow-elevated border border-slate-200 py-2">
                                @foreach ($item['children'] as $child)
                                    <a href="{{ route($child['route']) }}" class="block px-4 py-2 hover:bg-slate-50 text-slate-700">{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ route($item['route']) }}" class="text-slate-700 hover:text-moaum-red font-medium transition {{ request()->routeIs($item['route']) ? 'text-moaum-red' : '' }}">{{ $item['label'] }}</a>
                    @endif
                @endforeach
            </nav>

            {{-- Desktop CTAs --}}
            <div class="hidden lg:flex items-center space-x-4">
                <a href="{{ route('contact.index', ['topic' => 'quote']) }}" class="text-moaum-blue font-medium hover:text-moaum-red transition">Request a Quote</a>
                <x-button href="{{ route('contact.index', ['topic' => 'service']) }}" size="md">Request a Service</x-button>
            </div>

            {{-- Mobile menu button --}}
            <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 rounded-md text-slate-700" aria-label="Toggle menu" :aria-expanded="mobileMenu">
                <template x-if="!mobileMenu"><span><x-icon name="menu" class="h-6 w-6" /></span></template>
                <template x-if="mobileMenu"><span x-cloak><x-icon name="x" class="h-6 w-6" /></span></template>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="mobileMenu" x-cloak x-transition class="lg:hidden bg-white border-t border-slate-200">
        <div class="px-4 pt-2 pb-6 space-y-1 max-h-[80vh] overflow-y-auto">
            @foreach ($nav as $item)
                <a href="{{ isset($item['route']) ? route($item['route']) : '#' }}" class="block px-3 py-3 text-base font-medium text-slate-700 hover:bg-slate-50 rounded-md">{{ $item['label'] }}</a>
            @endforeach
            <div class="pt-4 space-y-3">
                <x-button href="{{ route('contact.index', ['topic' => 'service']) }}" class="w-full">Request a Service</x-button>
                <x-button href="{{ route('contact.index', ['topic' => 'quote']) }}" variant="outline" class="w-full">Request a Quote</x-button>
            </div>
        </div>
    </div>
</header>
