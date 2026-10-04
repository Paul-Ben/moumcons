{{-- Admin → create / edit a project (PRD §14). --}}
@php($editing = $project->exists)

<x-layouts.admin :title="$editing ? 'Edit '.$project->title : 'New project'">
    <form method="POST" action="{{ $editing ? route('admin.projects.update', $project) : route('admin.projects.store') }}" class="space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? $project->title : 'New project'" :back="route('admin.projects.index')" back-label="All projects">
            @if ($editing && $project->isPublished())
                <a href="{{ route('projects.show', $project) }}" target="_blank" rel="noopener" class="btn-ghost text-sm border border-slate-200">
                    <x-icon name="external-link" class="w-4 h-4" /> View on site
                </a>
            @endif
        </x-admin.page-header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="card space-y-4">
                    <x-admin.input name="title" label="Title" :value="$project->title" required maxlength="255" />
                    <x-admin.input name="slug" label="URL slug" :value="$project->slug" maxlength="255" hint="Leave blank to generate from the title." />
                    <x-admin.textarea name="summary" label="Summary" :value="$project->summary" rows="2" maxlength="300"
                                      hint="One or two sentences for project cards." />
                    <x-admin.rich-text name="description" label="Description" :value="$project->description" />
                    <x-admin.rich-text name="scope" label="Scope of work" :value="$project->scope" hint="What MOAUM delivered — a bulleted list works well." />
                </div>

                <x-admin.gallery-picker :items="$project->images?->map(fn ($i) => ['image' => $i->image, 'caption' => $i->caption])->all() ?? []"
                                        hint="Shown as a gallery on the project page." />

                <x-admin.seo-fields :model="$project" />
            </div>

            <div class="space-y-6">
                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Publishing</h2>
                    <x-admin.publish-fields :model="$project" :can-publish="$canPublish" />
                    <x-admin.input name="sort_order" type="number" label="Display order" :value="$project->sort_order ?? 0" min="0" />
                </div>

                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Details</h2>
                    <x-admin.select name="status" label="Project status" :options="$statuses" :value="$project->status" required />
                    <x-admin.select name="business_division_id" label="Division" :options="$divisions" :value="$project->business_division_id" placeholder="— None —" />
                    <x-admin.input name="client" label="Client" :value="$project->client" hint="Only name clients who have agreed to be listed." />
                    <x-admin.input name="location" label="Location" :value="$project->location" />
                    <div class="grid grid-cols-2 gap-3">
                        <x-admin.input name="start_date" type="date" label="Start" :value="$project->start_date?->format('Y-m-d')" />
                        <x-admin.input name="completion_date" type="date" label="Completion" :value="$project->completion_date?->format('Y-m-d')" />
                    </div>
                    <x-admin.input name="tags" label="Tags" :value="implode(', ', $project->tags ?? [])" hint="Comma-separated, e.g. solar, rural, public sector." />
                </div>

                <div class="card">
                    <x-admin.media-picker name="featured_image" label="Featured image" :value="$project->featured_image" />
                </div>

                <div class="card">
                    <x-admin.form-actions :cancel="route('admin.projects.index')" :submit="$editing ? 'Save changes' : 'Create project'" />
                </div>
            </div>
        </div>
    </form>

    @if ($editing)
        @can('delete', $project)
            <div class="card mt-6 flex justify-end">
                <x-admin.delete-button :action="route('admin.projects.destroy', $project)" label="Delete project" confirm="Delete this project and its gallery?" />
            </div>
        @endcan
    @endif
</x-layouts.admin>
