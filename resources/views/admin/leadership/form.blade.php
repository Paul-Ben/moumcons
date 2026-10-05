{{-- Admin → add / edit a leadership team member (PRD §9). --}}
@php($editing = $member->exists)

<x-layouts.admin :title="$editing ? 'Edit '.$member->name : 'Add person'">
    <form method="POST" action="{{ $editing ? route('admin.leadership.update', $member) : route('admin.leadership.store') }}" class="space-y-6 max-w-3xl">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? $member->name : 'Add a leadership team member'" :back="route('admin.leadership.index')" back-label="Leadership team" />

        <div class="card space-y-4">
            <div class="grid sm:grid-cols-2 gap-4">
                <x-admin.input name="name" label="Full name" :value="$member->name" required maxlength="255" />
                <x-admin.input name="position" label="Position" :value="$member->position" required maxlength="255" placeholder="e.g. Managing Director" />
            </div>
            <x-admin.textarea name="bio" label="Short biography" :value="$member->bio" rows="5" maxlength="3000" />
            <x-admin.media-picker name="photo" label="Photo" :value="$member->photo" hint="A square portrait works best." />
            <div class="grid sm:grid-cols-2 gap-4 items-end">
                <x-admin.input name="sort_order" type="number" min="0" label="Display order" :value="$member->sort_order ?? 0" />
                <x-admin.checkbox name="is_published" label="Show on the public site" :checked="$member->is_published" />
            </div>
        </div>

        <div class="card">
            <x-admin.form-actions :cancel="route('admin.leadership.index')" :submit="$editing ? 'Save' : 'Add person'" />
        </div>
    </form>

    @if ($editing)
        @can('delete', $member)
            <div class="card mt-6 max-w-3xl flex justify-end">
                <x-admin.delete-button :action="route('admin.leadership.destroy', $member)" label="Remove" confirm="Remove this person from the leadership page?" />
            </div>
        @endcan
    @endif
</x-layouts.admin>
