@props(['model', 'canPublish' => false, 'featured' => true])

{{--
    "Visible on site" + go-live date (App\Http\Requests\Concerns\HandlesPublication)
    and the featured flag. Read-only for staff without the publish permission.
--}}
@php
    $isPublished = old('publish', $model->published_at !== null);
    $date = old('published_at', $model->published_at?->format('Y-m-d\TH:i'));
@endphp

@if ($canPublish)
    <div x-data="{ publish: @js((bool) $isPublished) }" class="space-y-3">
        <label class="inline-flex items-start gap-2 text-sm text-slate-700">
            <input type="hidden" name="publish" value="0">
            <input type="checkbox" name="publish" value="1" x-model="publish"
                   class="mt-0.5 rounded border-slate-300 text-moaum-blue focus:ring-moaum-blue">
            <span><span class="font-medium">Visible on the public site</span>
                <span class="block text-xs text-slate-400">Untick to keep it as an unpublished draft.</span></span>
        </label>
        <div x-show="publish" x-cloak>
            <x-admin.input name="published_at" type="datetime-local" label="Publish date" :value="$date"
                           hint="Leave blank to publish now. A future date schedules it." />
        </div>
        @if ($featured)
            <x-admin.checkbox name="featured" label="Feature on the home page" :checked="$model->featured" />
        @endif
    </div>
@else
    <div class="text-sm text-slate-600 space-y-1">
        <p>Publication: <span class="badge {{ $model->publicationBadgeClasses() }}">{{ $model->publicationLabel() }}</span></p>
        <p class="text-xs text-slate-400">Publishing needs the publish permission for this section.</p>
    </div>
@endif
