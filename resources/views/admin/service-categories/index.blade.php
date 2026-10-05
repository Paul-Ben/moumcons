{{-- Admin → Service categories (PRD §11). --}}
<x-layouts.admin title="Service Categories">
    <div class="space-y-6">
        <x-admin.page-header title="Service Categories" description="Cross-division groupings used to filter the services catalogue."
            :back="route('admin.services.index')" back-label="Services" />

        <x-admin.category-manager :categories="$categories" route-prefix="admin.service-categories"
            :model="App\Models\ServiceCategory::class" noun="service" count-attribute="services_count"
            delete-warning="Delete this category? Its services become uncategorised." />
    </div>
</x-layouts.admin>
