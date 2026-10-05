{{-- Admin → search results (top-bar search). --}}
<x-layouts.admin title="Search">
    <div class="space-y-6 max-w-4xl">
        <x-admin.page-header title="Search" :description="$term ? 'Results for “'.$term.'”' : 'Search references, people and content.'" />

        @if (mb_strlen($term) < 2)
            <p class="text-sm text-slate-500">Type at least two characters, e.g. a reference like ENQ-261004 or a customer's name.</p>
        @elseif (empty($groups))
            <div class="card text-center py-12 text-slate-500">Nothing matched “{{ $term }}” in the areas you can access.</div>
        @else
            @foreach ($groups as $label => $rows)
                <div class="card overflow-hidden p-0">
                    <h2 class="px-4 py-3 border-b border-slate-100 font-semibold text-moaum-charcoal">{{ $label }}</h2>
                    @foreach ($rows as $row)
                        <a href="{{ $row['url'] }}" class="px-4 py-3 border-b border-slate-100 last:border-0 flex items-center gap-3 hover:bg-slate-50">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-moaum-charcoal truncate">{{ $row['title'] }}</p>
                                @if ($row['detail'])
                                    <p class="text-xs text-slate-400 truncate">{{ $row['detail'] }}</p>
                                @endif
                            </div>
                            <span class="text-xs text-slate-500 shrink-0">{{ $row['status'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>
</x-layouts.admin>
