{{-- Admin → Leadership team (PRD §9). --}}
<x-layouts.admin title="Leadership">
    <div class="space-y-6">
        <x-admin.page-header title="Leadership Team" description="Shown on the About → Leadership page, in display order."
            :back="route('admin.pages.index')" back-label="Pages">
            @can('create', App\Models\LeadershipMember::class)
                <a href="{{ route('admin.leadership.create') }}" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> Add person</a>
            @endcan
        </x-admin.page-header>

        <div class="card overflow-hidden p-0 divide-y divide-slate-100">
            @forelse ($members as $member)
                <div class="px-4 py-3 flex items-center gap-4">
                    <span class="w-12 h-12 rounded-full bg-slate-100 overflow-hidden flex items-center justify-center shrink-0 font-semibold text-slate-500">
                        @if ($member->photo)
                            <img src="{{ $member->photo }}" alt="" class="w-full h-full object-cover">
                        @else
                            {{ $member->initials() }}
                        @endif
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-moaum-charcoal">{{ $member->name }}</p>
                        <p class="text-sm text-slate-500">{{ $member->position }}</p>
                    </div>
                    <span class="text-xs text-slate-400">#{{ $member->sort_order }}</span>
                    @unless ($member->is_published)
                        <x-badge color="slate">Hidden</x-badge>
                    @endunless
                    @can('update', $member)
                        <a href="{{ route('admin.leadership.edit', $member) }}" class="text-moaum-blue hover:text-moaum-red font-medium text-sm">Edit</a>
                    @endcan
                </div>
            @empty
                <p class="px-4 py-10 text-center text-sm text-slate-500">No one added yet. The public page shows the intro text until the team is listed.</p>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
