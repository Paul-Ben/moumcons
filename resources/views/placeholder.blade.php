<x-layouts.public :title="config('moaum.company.name')">
    <x-container class="py-24 text-center">
        <p class="eyebrow mb-2">Coming in an upcoming module</p>
        <h1 class="font-display text-3xl font-bold text-moaum-charcoal mb-4">{{ request()->path() }}</h1>
        <p class="text-slate-600 max-w-xl mx-auto">This page is part of the MOAUM build plan and will be implemented with database-driven content in its assigned module.</p>
        <div class="mt-8">
            <x-button href="{{ route('home') }}" variant="outline">Back to Home</x-button>
        </div>
    </x-container>
</x-layouts.public>
