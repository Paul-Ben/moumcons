{{-- Admin → News & announcements (PRD §16). --}}
<x-layouts.admin title="News">
    <div class="space-y-6">
        <x-admin.page-header title="News & Announcements" description="Drafts and articles awaiting review are listed first.">
            @can('viewAny', App\Models\NewsCategory::class)
                <a href="{{ route('admin.news-categories.index') }}" class="btn-ghost text-sm border border-slate-200"><x-icon name="folder" class="w-4 h-4" /> Categories</a>
            @endcan
            @can('create', App\Models\NewsArticle::class)
                <a href="{{ route('admin.news.create') }}" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> New article</a>
            @endcan
        </x-admin.page-header>

        @if ($inReview > 0)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                {{ $inReview }} {{ Str::plural('article', $inReview) }} waiting for review.
                <a href="{{ route('admin.news.index', ['status' => 'review']) }}" class="font-semibold underline">Show them</a>
            </div>
        @endif

        <form method="GET" class="card p-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
            <div class="lg:col-span-2">
                <label for="q" class="form-label">Search</label>
                <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search titles…" class="form-input text-sm">
            </div>
            <x-admin.select name="status" label="Status" :options="$statuses" :value="$filters['status'] ?? null" placeholder="All statuses" />
            <x-admin.select name="category" label="Category" :options="$categories" :value="$filters['category'] ?? null" placeholder="All categories" />
            <div class="sm:col-span-2 lg:col-span-4 flex gap-2">
                <button type="submit" class="btn-primary text-sm"><x-icon name="sliders-horizontal" class="w-4 h-4" /> Apply</button>
                @if (collect($filters)->filter()->isNotEmpty())
                    <a href="{{ route('admin.news.index') }}" class="btn-ghost text-sm">Clear</a>
                @endif
            </div>
        </form>

        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Article</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Author</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Publish date</th>
                            <th class="px-4 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($articles as $article)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 max-w-md">
                                    <span class="block font-medium text-moaum-charcoal truncate">{{ $article->title }}</span>
                                    @if ($article->featured)
                                        <x-badge color="blue" class="mt-1">Featured</x-badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $article->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $article->author?->name ?? '—' }}</td>
                                <td class="px-4 py-3"><span class="badge {{ $article->status->badgeClasses() }}">{{ $article->status->label() }}</span></td>
                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $article->published_at?->format('d M Y, H:i') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if ($article->isPublished())
                                        <a href="{{ route('news.show', $article) }}" target="_blank" rel="noopener" class="text-slate-400 hover:text-moaum-blue mr-3" title="View on site">
                                            <x-icon name="external-link" class="w-4 h-4 inline" /><span class="sr-only">View on site</span>
                                        </a>
                                    @endif
                                    @can('update', $article)
                                        <a href="{{ route('admin.news.edit', $article) }}" class="text-moaum-blue hover:text-moaum-red font-medium">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">
                                <x-icon name="newspaper" class="w-8 h-8 mx-auto mb-3 text-slate-300" />
                                No articles yet.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($articles->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">{{ $articles->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin>
