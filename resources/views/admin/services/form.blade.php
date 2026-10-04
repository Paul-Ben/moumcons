{{-- Admin → create / edit a service (PRD §11). --}}
@php($editing = $service->exists)

<x-layouts.admin :title="$editing ? 'Edit '.$service->name : 'New service'">
    <form method="POST" action="{{ $editing ? route('admin.services.update', $service) : route('admin.services.store') }}" class="space-y-6"
          x-data="{ pricing: @js(old('pricing_type', $service->pricing_type?->value)) }">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? $service->name : 'New service'" :back="route('admin.services.index')" back-label="All services">
            @if ($editing && $service->status === App\Enums\ServiceStatus::Active)
                <a href="{{ route('services.show', $service) }}" target="_blank" rel="noopener" class="btn-ghost text-sm border border-slate-200">
                    <x-icon name="external-link" class="w-4 h-4" /> View on site
                </a>
            @endif
        </x-admin.page-header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="card space-y-4">
                    <x-admin.input name="name" label="Name" :value="$service->name" required maxlength="255" />
                    <x-admin.input name="slug" label="URL slug" :value="$service->slug" maxlength="255"
                                   hint="Leave blank to generate from the name." />
                    <x-admin.textarea name="short_description" label="Short description" :value="$service->short_description" rows="2" maxlength="255" />
                    <x-admin.rich-text name="description" label="Description" :value="$service->description" />
                </div>

                <div class="card space-y-4">
                    <div>
                        <h2 class="font-semibold text-moaum-charcoal">Pricing</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Only Fixed and Starting From show a price publicly. Publish a price only once the business manager has approved it.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.select name="pricing_type" label="Pricing type" :options="$pricingTypes" :value="$service->pricing_type" required x-model="pricing" />
                        <div x-show="pricing === 'fixed' || pricing === 'starting_from'" x-cloak>
                            <x-admin.input name="starting_price" type="number" step="0.01" min="0" label="Price (₦)" :value="$service->starting_price" />
                        </div>
                    </div>
                </div>

                <x-admin.seo-fields :model="$service" />
            </div>

            <div class="space-y-6">
                <div class="card space-y-4">
                    <h2 class="font-semibold text-moaum-charcoal">Publishing</h2>
                    @if ($canPublish)
                        <x-admin.select name="status" label="Status" :options="$statuses" :value="$service->status" required
                                        hint="Only Active services appear on the public site." />
                        <x-admin.checkbox name="featured" label="Feature on the home page" :checked="$service->featured" />
                    @else
                        <p class="text-sm text-slate-600">Status: <span class="badge {{ $service->status->badgeClasses() }}">{{ $service->status->label() }}</span></p>
                        <p class="text-xs text-slate-400">Changing status needs the publish-services permission.</p>
                    @endif
                    <x-admin.select name="business_division_id" label="Division" :options="$divisions" :value="$service->business_division_id" placeholder="Choose a division" required />
                    <x-admin.select name="service_category_id" label="Category" :options="$categories" :value="$service->service_category_id" placeholder="— None —" />
                    <x-admin.input name="service_type" label="Service type" :value="$service->service_type" placeholder="e.g. Consultancy, Supply, Training" />
                    <x-admin.input name="sort_order" type="number" label="Display order" :value="$service->sort_order ?? 0" min="0" />
                </div>

                <div class="card">
                    <x-admin.media-picker name="image" label="Image" :value="$service->image" />
                </div>

                <div class="card">
                    <x-admin.form-actions :cancel="route('admin.services.index')" :submit="$editing ? 'Save changes' : 'Create service'" />
                </div>
            </div>
        </div>
    </form>

    @if ($editing)
        @can('delete', $service)
            <div class="card mt-6 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-slate-600">Past requests for this service keep their record but lose the link to it.</p>
                <x-admin.delete-button :action="route('admin.services.destroy', $service)" label="Delete service" confirm="Delete this service permanently?" />
            </div>
        @endcan
    @endif
</x-layouts.admin>
