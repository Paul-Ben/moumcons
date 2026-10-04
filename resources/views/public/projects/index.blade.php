{{-- PRD §14 — project portfolio. --}}
<x-layouts.public title="Projects | {{ config('moaum.company.short_name') }}"
                  meta-description="Projects delivered by MOAUM Consultancy Services across construction, technology, agriculture, training and more.">

    <section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 border-b border-slate-200">
        <x-container class="py-16 lg:py-20">
            <nav aria-label="Breadcrumb" class="flex text-sm text-slate-500 mb-4">
                <a href="{{ route('home') }}" class="hover:text-moaum-red">Home</a>
                <span class="mx-2">/</span>
                <span class="text-moaum-charcoal font-medium" aria-current="page">Projects</span>
            </nav>
            <h1 class="font-display text-4xl lg:text-5xl font-bold text-moaum-charcoal mb-3">Our Projects</h1>
            <p class="text-lg text-slate-600 max-w-2xl">Work delivered across MOAUM's business divisions.</p>
        </x-container>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <form method="GET" action="{{ route('projects.index') }}" class="mb-10 flex flex-wrap items-end gap-3">
            <div>
                <label for="division" class="block text-sm font-medium text-slate-600 mb-1">Division</label>
                <select id="division" name="division" class="py-3 px-4 rounded-lg border border-slate-300 bg-white focus:border-moaum-blue outline-none">
                    <option value="">All divisions</option>
                    @foreach ($divisions as $division)
                        <option value="{{ $division->slug }}" @selected($activeDivision?->is($division))>{{ $division->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-slate-600 mb-1">Status</label>
                <select id="status" name="status" class="py-3 px-4 rounded-lg border border-slate-300 bg-white focus:border-moaum-blue outline-none">
                    <option value="">Any status</option>
                    <option value="ongoing" @selected($activeStatus === 'ongoing')>Ongoing</option>
                    <option value="completed" @selected($activeStatus === 'completed')>Completed</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Filter</button>
            @if ($activeDivision || $activeStatus)
                <a href="{{ route('projects.index') }}" class="btn-ghost">Clear</a>
            @endif
        </form>

        @if ($projects->isEmpty())
            <div class="text-center py-20 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                <x-icon name="hard-hat" class="w-12 h-12 mx-auto text-slate-300 mb-4" />
                <p class="text-slate-600">No projects to show yet. Please check back soon.</p>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($projects as $project)
                    <x-project-card :project="$project" />
                @endforeach
            </div>
            @if ($projects->hasPages())
                <div class="mt-12">{{ $projects->links() }}</div>
            @endif
        @endif
    </section>
</x-layouts.public>
