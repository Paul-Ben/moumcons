@props(['model' => null])

{{-- PRD §34 — per-record SEO overrides; blank falls back to the record's own title/summary. --}}
<div class="card space-y-4">
    <div>
        <h2 class="font-semibold text-moaum-charcoal">Search &amp; sharing</h2>
        <p class="text-xs text-slate-400 mt-0.5">Optional. Leave blank to use the title and summary.</p>
    </div>
    <x-admin.input name="seo_title" label="SEO title" :value="$model?->seo_title" maxlength="70"
                   hint="Shown in search results and browser tabs. Around 60 characters." />
    <x-admin.textarea name="seo_description" label="Meta description" :value="$model?->seo_description" rows="3" maxlength="300"
                      hint="One or two sentences, around 155 characters." />
</div>
