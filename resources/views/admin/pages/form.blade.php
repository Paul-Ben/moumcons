{{-- Admin → create / edit a CMS page (PRD §22). --}}
@php($editing = $page->exists)

<x-layouts.admin :title="$editing ? 'Edit '.$page->title : 'New page'">
    <form method="POST" action="{{ $editing ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? $page->title : 'New page'" :back="route('admin.pages.index')" back-label="All pages"
            :description="$page->isSystem() ? 'Site page — its address is fixed.' : null">
            @if ($editing && $page->isPublished())
                <a href="{{ $page->url() }}" target="_blank" rel="noopener" class="btn-ghost text-sm border border-slate-200">
                    <x-icon name="external-link" class="w-4 h-4" /> View on site
                </a>
            @endif
        </x-admin.page-header>

        @if (str_contains((string) $page->content, 'CLIENT_TO_PROVIDE'))
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                This page contains <strong>CLIENT_TO_PROVIDE</strong> placeholders waiting for approved copy from MOAUM.
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="card space-y-4">
                    <x-admin.input name="title" label="Title" :value="$page->title" required maxlength="255" />
                    @unless ($page->isSystem())
                        <x-admin.input name="slug" label="URL slug" :value="$page->slug" maxlength="255"
                                       hint="The page is served at /pages/your-slug. Leave blank to generate from the title." />
                    @endunless
                    <x-admin.textarea name="summary" label="Intro" :value="$page->summary" rows="2" maxlength="300"
                                      hint="Shown under the page title." />
                    <x-admin.rich-text name="content" label="Content" :value="$page->content" />
                </div>
                <x-admin.seo-fields :model="$page" />
            </div>

            <div class="space-y-6">
                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Publishing</h2>
                    @if ($canPublish)
                        <x-admin.select name="status" label="Status" :options="$statuses" :value="$page->status" required />
                    @else
                        <p class="text-sm text-slate-600">Status: <span class="badge {{ $page->status->badgeClasses() }}">{{ $page->status->label() }}</span></p>
                        <p class="text-xs text-slate-400">Changing status needs the publish-pages permission.</p>
                    @endif
                </div>
                <div class="card">
                    <x-admin.media-picker name="hero_image" label="Header image" :value="$page->hero_image" />
                </div>
                <div class="card">
                    <x-admin.form-actions :cancel="route('admin.pages.index')" :submit="$editing ? 'Save page' : 'Create page'" />
                </div>
            </div>
        </div>
    </form>

    @if ($editing)
        @can('delete', $page)
            <div class="card mt-6 flex justify-end">
                <x-admin.delete-button :action="route('admin.pages.destroy', $page)" label="Delete page" confirm="Delete this page permanently?" />
            </div>
        @endcan
    @endif
</x-layouts.admin>
