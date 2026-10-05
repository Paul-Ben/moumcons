{{-- Admin → Pages (PRD §9/§22). --}}
<x-layouts.admin title="Pages">
    <div class="space-y-6">
        <x-admin.page-header title="Pages" description="About section, legal pages and any custom pages.">
            @can('viewAny', App\Models\LeadershipMember::class)
                <a href="{{ route('admin.leadership.index') }}" class="btn-ghost text-sm border border-slate-200"><x-icon name="users" class="w-4 h-4" /> Leadership team</a>
            @endcan
            @can('create', App\Models\Page::class)
                <a href="{{ route('admin.pages.create') }}" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> New page</a>
            @endcan
        </x-admin.page-header>

        @foreach (['Site pages' => $systemPages, 'Custom pages' => $customPages] as $heading => $pages)
            <div class="card overflow-hidden p-0">
                <h2 class="px-4 py-3 border-b border-slate-100 font-semibold text-moaum-charcoal">{{ $heading }}</h2>
                @forelse ($pages as $page)
                    <div class="px-4 py-3 border-b border-slate-100 last:border-0 flex flex-wrap items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-moaum-charcoal">{{ $page->title }}</p>
                            <p class="text-xs text-slate-400 font-mono truncate">{{ parse_url($page->url(), PHP_URL_PATH) }}</p>
                        </div>
                        @if (str_contains((string) $page->content, 'CLIENT_TO_PROVIDE'))
                            <x-badge color="warning">Needs client copy</x-badge>
                        @endif
                        <span class="badge {{ $page->status->badgeClasses() }}">{{ $page->status->label() }}</span>
                        @if ($page->isPublished())
                            <a href="{{ $page->url() }}" target="_blank" rel="noopener" class="text-slate-400 hover:text-moaum-blue" title="View on site">
                                <x-icon name="external-link" class="w-4 h-4" /><span class="sr-only">View on site</span>
                            </a>
                        @endif
                        @can('update', $page)
                            <a href="{{ route('admin.pages.edit', $page) }}" class="text-moaum-blue hover:text-moaum-red font-medium text-sm">Edit</a>
                        @endcan
                    </div>
                @empty
                    <p class="px-4 py-6 text-sm text-slate-500">
                        {{ $heading === 'Site pages' ? 'Run php artisan db:seed --class=PageSeeder to create the site pages.' : 'No custom pages yet.' }}
                    </p>
                @endforelse
            </div>
        @endforeach
    </div>
</x-layouts.admin>
