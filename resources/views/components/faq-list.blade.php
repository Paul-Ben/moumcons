@props(['faqs'])

{{-- Accessible FAQ accordion (button + region per question). --}}
<div {{ $attributes->merge(['class' => 'divide-y divide-slate-200 border-y border-slate-200']) }}>
    @foreach ($faqs as $faq)
        <div x-data="{ open: false }">
            <h3>
                <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="faq-{{ $faq->id }}"
                        class="w-full flex items-center justify-between gap-4 py-5 text-left font-semibold text-moaum-charcoal hover:text-moaum-blue transition">
                    <span>{{ $faq->question }}</span>
                    <span class="shrink-0 transition-transform" :class="open && 'rotate-180'">
                        <x-icon name="chevron-down" class="w-5 h-5" />
                    </span>
                </button>
            </h3>
            <div id="faq-{{ $faq->id }}" x-show="open" x-cloak role="region" class="pb-5">
                <x-rich-content :html="$faq->answer" class="text-slate-600" />
            </div>
        </div>
    @endforeach
</div>
