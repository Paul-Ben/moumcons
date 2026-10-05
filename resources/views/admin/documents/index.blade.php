{{-- Admin → Downloads / document library (PRD §18). --}}
<x-layouts.admin title="Downloads">
    <div class="space-y-6">
        <x-admin.page-header title="Downloads" description="Brochures, forms, reports and other files offered on /downloads.">
            @can('create', App\Models\Document::class)
                <a href="{{ route('admin.documents.create') }}" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> Upload document</a>
            @endcan
        </x-admin.page-header>

        <form method="GET" class="flex flex-wrap gap-2">
            <label for="q" class="sr-only">Search</label>
            <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search titles…" class="form-input text-sm max-w-xs">
            <label for="category" class="sr-only">Category</label>
            <select id="category" name="category" class="form-input text-sm max-w-[14rem]">
                <option value="">All categories</option>
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['category'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-ghost text-sm border border-slate-200">Filter</button>
        </form>

        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Document</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Access</th>
                            <th class="px-4 py-3 text-right">Downloads</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($documents as $document)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-10 h-10 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-[10px] font-bold shrink-0">{{ $document->fileType() }}</span>
                                        <div class="min-w-0">
                                            <span class="block font-medium text-moaum-charcoal truncate">{{ $document->title }}</span>
                                            <span class="block text-xs text-slate-400">{{ $document->humanSize() }}{{ $document->division ? ' · '.$document->division->name : '' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $document->category->label() }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $document->access_level->label() }}</td>
                                <td class="px-4 py-3 text-right text-slate-600">{{ number_format($document->download_count) }}</td>
                                <td class="px-4 py-3">
                                    <span class="badge {{ $document->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $document->is_published ? 'Published' : 'Hidden' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.documents.file', $document) }}" class="text-slate-400 hover:text-moaum-blue mr-3" title="Download file">
                                        <x-icon name="download" class="w-4 h-4 inline" /><span class="sr-only">Download file</span>
                                    </a>
                                    @can('update', $document)
                                        <a href="{{ route('admin.documents.edit', $document) }}" class="text-moaum-blue hover:text-moaum-red font-medium">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">
                                <x-icon name="download" class="w-8 h-8 mx-auto mb-3 text-slate-300" />
                                No documents uploaded yet.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($documents->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $documents->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin>
