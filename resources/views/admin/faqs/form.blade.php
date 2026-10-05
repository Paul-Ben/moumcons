{{-- Admin → add / edit an FAQ (PRD §22). --}}
@php($editing = $faq->exists)

<x-layouts.admin :title="$editing ? 'Edit FAQ' : 'New FAQ'">
    <form method="POST" action="{{ $editing ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="space-y-6 max-w-4xl">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.page-header :title="$editing ? 'Edit FAQ' : 'New FAQ'" :back="route('admin.faqs.index')" back-label="All FAQs" />

        <div class="card space-y-4">
            <x-admin.input name="question" label="Question" :value="$faq->question" required maxlength="500" />
            <x-admin.rich-text name="answer" label="Answer" :value="$faq->answer" required />
        </div>

        <div class="card space-y-4">
            <div class="grid sm:grid-cols-3 gap-4">
                <x-admin.select name="business_division_id" label="Division" :options="$divisions" :value="$faq->business_division_id" placeholder="General" />
                <x-admin.input name="category" label="Topic" :value="$faq->category" list="faq-categories" placeholder="e.g. Payments" />
                <x-admin.input name="sort_order" type="number" min="0" label="Display order" :value="$faq->sort_order ?? 0" />
            </div>
            <datalist id="faq-categories">
                @foreach ($categories as $category)
                    <option value="{{ $category }}"></option>
                @endforeach
            </datalist>
            @if ($canPublish)
                <x-admin.checkbox name="is_published" label="Show on the public site" :checked="$faq->is_published" />
            @endif
        </div>

        <div class="card">
            <x-admin.form-actions :cancel="route('admin.faqs.index')" :submit="$editing ? 'Save' : 'Add FAQ'" />
        </div>
    </form>

    @if ($editing)
        @can('delete', $faq)
            <div class="card mt-6 max-w-4xl flex justify-end">
                <x-admin.delete-button :action="route('admin.faqs.destroy', $faq)" label="Delete FAQ" />
            </div>
        @endcan
    @endif
</x-layouts.admin>
