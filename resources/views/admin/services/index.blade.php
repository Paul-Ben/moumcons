{{-- Admin → Services catalogue (PRD §11). --}}
<x-layouts.admin title="Services">
    <div class="space-y-6">
        <x-admin.page-header title="Services" description="Every service belongs to a business division.">
            @can('viewAny', App\Models\ServiceCategory::class)
                <a href="{{ route('admin.service-categories.index') }}" class="btn-ghost text-sm border border-slate-200"><x-icon name="folder" class="w-4 h-4" /> Categories</a>
            @endcan
            @can('create', App\Models\Service::class)
                <a href="{{ route('admin.services.create', array_filter(['division' => $filters['division'] ?? null])) }}" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> New service</a>
            @endcan
        </x-admin.page-header>

        <form method="GET" class="card p-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <label for="q" class="sr-only">Search</label>
                <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search services…" class="form-input text-sm">
            </div>
            <x-admin.select name="division" label="Division" :options="$divisions" :value="$filters['division'] ?? null" placeholder="All divisions" />
            <x-admin.select name="category" label="Category" :options="$categories" :value="$filters['category'] ?? null" placeholder="All categories" />
            <x-admin.select name="status" label="Status" :options="$statuses" :value="$filters['status'] ?? null" placeholder="All statuses" />
            <div class="sm:col-span-2 lg:col-span-5 flex gap-2">
                <button type="submit" class="btn-primary text-sm"><x-icon name="sliders-horizontal" class="w-4 h-4" /> Apply</button>
                @if (collect($filters)->filter()->isNotEmpty())
                    <a href="{{ route('admin.services.index') }}" class="btn-ghost text-sm">Clear</a>
                @endif
            </div>
        </form>

        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3">Division</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Pricing</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($services as $service)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-moaum-charcoal">{{ $service->name }}</span>
                                    @if ($service->featured)
                                        <x-badge color="blue" class="ml-2">Featured</x-badge>
                                    @endif
                                    <span class="block text-xs text-slate-400 font-mono">/services/{{ $service->slug }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $service->division?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $service->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                    {{ $service->pricing_type->label() }}
                                    @if ($service->pricing_type->priceIsPublic() && $service->starting_price !== null)
                                        <span class="block text-xs text-slate-400">₦{{ number_format((float) $service->starting_price) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3"><span class="badge {{ $service->status->badgeClasses() }}">{{ $service->status->label() }}</span></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @can('update', $service)
                                        <a href="{{ route('admin.services.edit', $service) }}" class="text-moaum-blue hover:text-moaum-red font-medium">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">No services match.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($services->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $services->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin>
