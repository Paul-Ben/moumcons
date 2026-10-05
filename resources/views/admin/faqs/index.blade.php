{{-- Admin → FAQs (PRD §22). --}}
<x-layouts.admin title="FAQs">
    <div class="space-y-6">
        <x-admin.page-header title="FAQs" description="General questions appear on /faqs; division questions also show on that division's page.">
            @can('create', App\Models\Faq::class)
                <a href="{{ route('admin.faqs.create', is_numeric($division) ? ['division' => $division] : []) }}" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> New FAQ</a>
            @endcan
        </x-admin.page-header>

        <form method="GET" class="flex flex-wrap gap-2">
            <label for="division" class="sr-only">Division</label>
            <select id="division" name="division" class="form-input text-sm max-w-xs" onchange="this.form.submit()">
                <option value="">All FAQs</option>
                <option value="general" @selected($division === 'general')>General (no division)</option>
                @foreach ($divisions as $id => $name)
                    <option value="{{ $id }}" @selected((string) $division === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
            <noscript><button type="submit" class="btn-ghost text-sm border border-slate-200">Filter</button></noscript>
        </form>

        <div class="card overflow-hidden p-0 divide-y divide-slate-100">
            @forelse ($faqs as $faq)
                <div class="px-4 py-3 flex flex-wrap items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-moaum-charcoal">{{ $faq->question }}</p>
                        <p class="text-xs text-slate-400">
                            {{ $faq->division?->name ?? 'General' }}{{ $faq->category ? ' · '.$faq->category : '' }} · order {{ $faq->sort_order }}
                        </p>
                    </div>
                    @unless ($faq->is_published)
                        <x-badge color="slate">Hidden</x-badge>
                    @endunless
                    @can('update', $faq)
                        <a href="{{ route('admin.faqs.edit', $faq) }}" class="text-moaum-blue hover:text-moaum-red font-medium text-sm">Edit</a>
                    @endcan
                </div>
            @empty
                <p class="px-4 py-10 text-center text-sm text-slate-500">No FAQs yet.</p>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
