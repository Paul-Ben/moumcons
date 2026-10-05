{{-- PRD §15 — training & capacity-building catalogue. --}}
<x-layouts.public title="Training | {{ config('moaum.company.short_name') }}"
                  meta-description="Professional training and capacity-building programmes from MOAUM Consultancy Services — physical, online and hybrid.">

    <section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
        <x-container class="py-16 lg:py-20">
            <nav aria-label="Breadcrumb" class="flex text-sm text-slate-500 mb-4">
                <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a>
                <span class="mx-2">/</span>
                <span class="text-moaum-charcoal font-medium" aria-current="page">Training</span>
            </nav>
            <h1 class="font-display text-4xl lg:text-5xl font-bold text-moaum-charcoal mb-3">Training &amp; Capacity Building</h1>
            <p class="text-lg text-slate-600 max-w-2xl">Practical programmes for individuals, teams and institutions.</p>
        </x-container>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <form method="GET" action="{{ route('training.index') }}" class="mb-10 flex flex-wrap items-end gap-3">
            @if ($categories->isNotEmpty())
                <div>
                    <label for="category" class="block text-sm font-medium text-slate-600 mb-1">Category</label>
                    <select id="category" name="category" class="py-3 px-4 rounded-lg border border-slate-300 bg-white focus:border-moaum-blue outline-none">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(($filters['category'] ?? null) === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label for="mode" class="block text-sm font-medium text-slate-600 mb-1">Delivery</label>
                <select id="mode" name="mode" class="py-3 px-4 rounded-lg border border-slate-300 bg-white focus:border-moaum-blue outline-none">
                    <option value="">Any mode</option>
                    @foreach ($modes as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['mode'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if ($showPast)
                <input type="hidden" name="past" value="1">
            @endif
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('training.index', $showPast ? [] : ['past' => 1]) }}" class="btn-ghost ml-auto">
                {{ $showPast ? 'Show upcoming programmes' : 'View past programmes' }}
            </a>
        </form>

        @if ($programmes->isEmpty())
            <div class="text-center py-20 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                <x-icon name="graduation-cap" class="w-12 h-12 mx-auto text-slate-300 mb-4" />
                <p class="text-slate-600 mb-4">{{ $showPast ? 'No past programmes to show.' : 'No upcoming programmes are scheduled right now.' }}</p>
                <a href="{{ route('contact.index') }}" class="text-moaum-blue font-semibold hover:underline">Ask us about custom training for your organisation</a>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($programmes as $programme)
                    <x-training-card :programme="$programme" />
                @endforeach
            </div>
            @if ($programmes->hasPages())
                <div class="mt-12">{{ $programmes->links() }}</div>
            @endif
        @endif
    </section>
</x-layouts.public>
