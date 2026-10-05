{{-- Admin → News categories (PRD §16). --}}
<x-layouts.admin title="News Categories">
    <div class="space-y-6">
        <x-admin.page-header title="News Categories" description="e.g. Announcements, Events, Publications."
            :back="route('admin.news.index')" back-label="News" />

        <x-admin.category-manager :categories="$categories" route-prefix="admin.news-categories"
            :model="App\Models\NewsCategory::class" noun="article" count-attribute="articles_count"
            delete-warning="Delete this category? Its articles become uncategorised." />
    </div>
</x-layouts.admin>
