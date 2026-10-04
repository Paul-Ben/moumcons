@props(['project'])

{{-- Portfolio card (PRD §14) used on /projects, the home page and division pages. --}}
<a href="{{ route('projects.show', $project) }}"
   {{ $attributes->merge(['class' => 'group block bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-card hover:shadow-lg hover:-translate-y-0.5 transition']) }}>
    <div class="aspect-[16/10] bg-slate-100 overflow-hidden">
        @if ($project->featured_image)
            <img src="{{ $project->featured_image }}" alt="" loading="lazy"
                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500 motion-reduce:transform-none">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
                <x-icon name="hard-hat" class="w-12 h-12 text-slate-300" />
            </div>
        @endif
    </div>
    <div class="p-6">
        <div class="flex flex-wrap items-center gap-2 mb-3 text-xs">
            <span class="badge {{ $project->status->badgeClasses() }}">{{ $project->status->label() }}</span>
            @if ($project->division)
                <span class="text-slate-500">{{ $project->division->name }}</span>
            @endif
        </div>
        <h3 class="font-display text-lg font-bold text-moaum-charcoal group-hover:text-moaum-blue transition mb-2">{{ $project->title }}</h3>
        @if ($project->summary)
            <p class="text-sm text-slate-600 line-clamp-3">{{ $project->summary }}</p>
        @endif
        @if ($project->location || $project->period())
            <p class="mt-4 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                @if ($project->location)
                    <span class="inline-flex items-center gap-1"><x-icon name="map-pin" class="w-3.5 h-3.5" /> {{ $project->location }}</span>
                @endif
                @if ($project->period())
                    <span class="inline-flex items-center gap-1"><x-icon name="calendar" class="w-3.5 h-3.5" /> {{ $project->period() }}</span>
                @endif
            </p>
        @endif
    </div>
</a>
