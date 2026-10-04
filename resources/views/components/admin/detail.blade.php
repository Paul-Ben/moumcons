@props(['label'])

{{-- One label/value pair inside a <dl> on admin detail screens. --}}
<div {{ $attributes }}>
    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</dt>
    <dd class="mt-0.5 text-slate-600 break-words">{{ $slot }}</dd>
</div>
