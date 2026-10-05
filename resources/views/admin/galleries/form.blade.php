{{-- Admin → create / edit a gallery album (PRD §19). --}}
@php($editing = $gallery->exists)

<x-layouts.admin :title="$editing ? 'Edit '.$gallery->title : 'New album'">
    <form method="POST" action="{{ $editing ? route('admin.galleries.update', $gallery) : route('admin.galleries.store') }}" class="space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? $gallery->title : 'New album'" :back="route('admin.galleries.index')" back-label="All albums">
            @if ($editing && $gallery->isPublished())
                <a href="{{ route('gallery.show', $gallery) }}" target="_blank" rel="noopener" class="btn-ghost text-sm border border-slate-200">
                    <x-icon name="external-link" class="w-4 h-4" /> View on site
                </a>
            @endif
        </x-admin.page-header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="card space-y-4">
                    <x-admin.input name="title" label="Album title" :value="$gallery->title" required maxlength="255" />
                    <x-admin.input name="slug" label="URL slug" :value="$gallery->slug" maxlength="255" hint="Leave blank to generate from the title." />
                    <x-admin.textarea name="description" label="Description" :value="$gallery->description" rows="2" maxlength="500" />
                </div>

                <x-admin.gallery-picker :items="$gallery->images?->map(fn ($i) => ['image' => $i->image, 'caption' => $i->caption])->all() ?? []"
                                        label="Photos" hint="Captions are shown in the lightbox and used as alt text." />
            </div>

            <div class="space-y-6">
                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Publishing</h2>
                    <x-admin.publish-fields :model="$gallery" :can-publish="$canPublish" :featured="false" />
                    <x-admin.input name="sort_order" type="number" label="Display order" :value="$gallery->sort_order ?? 0" min="0" />
                </div>

                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Details</h2>
                    <x-admin.select name="type" label="Album type" :options="$types" :value="$gallery->type" required />
                    <x-admin.select name="business_division_id" label="Division" :options="$divisions" :value="$gallery->business_division_id" placeholder="— None —" />
                    <x-admin.select name="project_id" label="Project" :options="$projects" :value="$gallery->project_id" placeholder="— None —" />
                    <x-admin.input name="event_date" type="date" label="Date" :value="$gallery->event_date?->format('Y-m-d')" />
                </div>

                <div class="card">
                    <x-admin.media-picker name="cover_image" label="Cover image" :value="$gallery->cover_image" hint="Defaults to the first photo." />
                </div>

                <div class="card">
                    <x-admin.form-actions :cancel="route('admin.galleries.index')" :submit="$editing ? 'Save album' : 'Create album'" />
                </div>
            </div>
        </div>
    </form>

    @if ($editing)
        @can('delete', $gallery)
            <div class="card mt-6 flex justify-end">
                <x-admin.delete-button :action="route('admin.galleries.destroy', $gallery)" label="Delete album" confirm="Delete this album? The photos stay in the media library." />
            </div>
        @endcan
    @endif
</x-layouts.admin>
