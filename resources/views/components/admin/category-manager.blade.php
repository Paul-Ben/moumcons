@props(['categories', 'routePrefix', 'model', 'noun', 'countAttribute', 'deleteWarning'])

{{--
    Inline add / rename / reorder / delete list for a small category table.
    $routePrefix e.g. 'admin.news-categories' (store, update, destroy routes);
    $model is the category class, for policy checks.
--}}
<div class="space-y-6">
    @can('create', $model)
        <form method="POST" action="{{ route($routePrefix.'.store') }}" class="card grid gap-4 md:grid-cols-[2fr_3fr_8rem_auto] md:items-end">
            @csrf
            <x-admin.input name="name" label="New category" required maxlength="255" />
            <x-admin.input name="description" label="Description" maxlength="255" />
            <x-admin.input name="sort_order" type="number" label="Order" value="0" min="0" />
            <button type="submit" class="btn-primary text-sm"><x-icon name="plus" class="w-4 h-4" /> Add</button>
        </form>
    @endcan

    <div class="card overflow-hidden p-0 divide-y divide-slate-100">
        @forelse ($categories as $category)
            <div class="p-4 flex flex-wrap items-center gap-3" x-data="{ editing: false }">
                <div class="flex-1 min-w-0" x-show="!editing">
                    <p class="font-medium text-moaum-charcoal">{{ $category->name }}</p>
                    <p class="text-xs text-slate-400">
                        {{ $category->description ?: 'No description' }} &middot;
                        {{ $category->{$countAttribute} }} {{ Str::plural($noun, $category->{$countAttribute}) }}
                    </p>
                </div>
                @can('update', $category)
                    <form x-show="editing" x-cloak method="POST" action="{{ route($routePrefix.'.update', $category) }}" class="flex-1 grid gap-2 sm:grid-cols-[2fr_3fr_6rem_auto]">
                        @csrf
                        @method('PUT')
                        <input name="name" value="{{ $category->name }}" required maxlength="255" class="form-input h-10 text-sm" aria-label="Name">
                        <input name="description" value="{{ $category->description }}" maxlength="255" class="form-input h-10 text-sm" aria-label="Description">
                        <input name="sort_order" type="number" min="0" value="{{ $category->sort_order }}" class="form-input h-10 text-sm" aria-label="Order">
                        <button type="submit" class="btn-secondary text-sm py-2 px-4">Save</button>
                    </form>
                    <button type="button" class="btn-ghost text-sm" @click="editing = !editing" x-text="editing ? 'Cancel' : 'Edit'"></button>
                @endcan
                @can('delete', $category)
                    <x-admin.delete-button :action="route($routePrefix.'.destroy', $category)" label="" :confirm="$deleteWarning" />
                @endcan
            </div>
        @empty
            <p class="p-8 text-center text-sm text-slate-500">No categories yet.</p>
        @endforelse
    </div>
</div>
