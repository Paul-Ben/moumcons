{{-- Admin → create / edit a business division (PRD §10). --}}
@php
    $editing = $division->exists;
    $capabilityRows = old('capabilities', $division->capabilities?->map(fn ($c) => ['id' => $c->id, 'title' => $c->title, 'description' => $c->description])->all() ?? []);
@endphp

<x-layouts.admin :title="$editing ? 'Edit '.$division->name : 'New division'">
    <form method="POST" action="{{ $editing ? route('admin.divisions.update', $division) : route('admin.divisions.store') }}" class="space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? $division->name : 'New division'" :back="route('admin.divisions.index')" back-label="All divisions">
            @if ($editing)
                <a href="{{ route('businesses.show', $division) }}" target="_blank" rel="noopener" class="btn-ghost text-sm border border-slate-200">
                    <x-icon name="external-link" class="w-4 h-4" /> View on site
                </a>
            @endif
        </x-admin.page-header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="card space-y-4">
                    <x-admin.input name="name" label="Name" :value="$division->name" required maxlength="255" />
                    <x-admin.input name="slug" label="URL slug" :value="$division->slug" maxlength="255"
                                   hint="Leave blank to generate from the name. Changing it breaks existing links." />
                    <x-admin.textarea name="short_description" label="Short description" :value="$division->short_description" rows="2" maxlength="255"
                                      hint="One line for cards and the division hero." />
                    <x-admin.rich-text name="full_description" label="Overview" :value="$division->full_description" />
                </div>

                {{-- Key capabilities (business-detail.html) --}}
                <div class="card space-y-4" x-data="{ rows: @js(array_values($capabilityRows)) }">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-semibold text-moaum-charcoal">Key capabilities</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Shown as a checklist on the division page, in this order.</p>
                        </div>
                        <button type="button" class="btn-ghost text-sm border border-slate-200" @click="rows.push({ id: null, title: '', description: '' })">
                            <x-icon name="plus" class="w-4 h-4" /> Add
                        </button>
                    </div>

                    <template x-for="(row, index) in rows" :key="index">
                        <div class="flex gap-3 items-start border border-slate-100 rounded-xl p-3">
                            <input type="hidden" :name="`capabilities[${index}][id]`" :value="row.id ?? ''">
                            <div class="flex-1 grid gap-2 sm:grid-cols-2">
                                <input type="text" :name="`capabilities[${index}][title]`" x-model="row.title" placeholder="Capability" maxlength="255"
                                       class="form-input h-10 text-sm" :aria-label="`Capability ${index + 1} title`">
                                <input type="text" :name="`capabilities[${index}][description]`" x-model="row.description" placeholder="Short description (optional)" maxlength="500"
                                       class="form-input h-10 text-sm" :aria-label="`Capability ${index + 1} description`">
                            </div>
                            <div class="flex flex-col">
                                <button type="button" class="btn-ghost p-1.5" @click="if (index > 0) { [rows[index - 1], rows[index]] = [rows[index], rows[index - 1]] }" :disabled="index === 0" aria-label="Move up">
                                    <x-icon name="chevron-down" class="w-4 h-4 rotate-180" />
                                </button>
                                <button type="button" class="btn-ghost p-1.5 text-moaum-red" @click="rows.splice(index, 1)" aria-label="Remove">
                                    <x-icon name="trash" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </template>
                    <p x-show="rows.length === 0" class="text-sm text-slate-400">No capabilities listed.</p>
                    @error('capabilities') <p class="form-error">{{ $message }}</p> @enderror
                    @error('capabilities.*.title') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Contact</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.input name="contact_email" type="email" label="Email" :value="$division->contact_email" />
                        <x-admin.input name="contact_phone" label="Phone" :value="$division->contact_phone" maxlength="30" />
                        <x-admin.input name="location" label="Location" :value="$division->location" />
                        <x-admin.input name="operating_hours" label="Operating hours" :value="$division->operating_hours" placeholder="Mon – Fri, 8am – 5pm" />
                    </div>
                </div>

                <x-admin.seo-fields :model="$division" />
            </div>

            <div class="space-y-6">
                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Publishing</h2>
                    @if ($canPublish)
                        <x-admin.select name="status" label="Status" :options="$statuses" :value="$division->status" required
                                        hint="Active and Coming Soon divisions appear on the public site." />
                        <x-admin.checkbox name="featured" label="Feature on the home page" :checked="$division->featured" />
                    @else
                        <p class="text-sm text-slate-600">Status: <span class="badge {{ $division->status->badgeClasses() }}">{{ $division->status->label() }}</span></p>
                        <p class="text-xs text-slate-400">Changing status needs the publish-divisions permission.</p>
                    @endif
                    <x-admin.select name="category" label="Group" :options="$categories" :value="$division->category" placeholder="— None —"
                                    hint="Where the division sits in the Our Businesses menu." />
                    <x-admin.input name="sort_order" type="number" label="Display order" :value="$division->sort_order ?? 0" min="0" />
                </div>

                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Imagery</h2>
                    <x-admin.media-picker name="cover_image" label="Card image" :value="$division->cover_image" hint="Used on division cards." />
                    <x-admin.media-picker name="hero_image" label="Hero image" :value="$division->hero_image" hint="Background of the division page header." />
                    <x-admin.select name="icon" label="Icon" :options="$icons" :value="$division->icon" placeholder="— Default —" />
                </div>

                <div class="card">
                    <x-admin.form-actions :cancel="route('admin.divisions.index')" :submit="$editing ? 'Save changes' : 'Create division'" />
                </div>
            </div>
        </div>
    </form>

    @if ($editing)
        @can('delete', $division)
            <div class="card mt-6 flex flex-wrap items-center justify-between gap-3 border-red-100">
                <p class="text-sm text-slate-600">Deleting is only possible for divisions with no services or customer requests. Otherwise archive it.</p>
                <x-admin.delete-button :action="route('admin.divisions.destroy', $division)" label="Delete division"
                    confirm="Delete this division permanently?" />
            </div>
        @endcan
    @endif
</x-layouts.admin>
