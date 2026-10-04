@props(['series' => [], 'empty' => 'Nothing open yet'])

{{-- Horizontal share-of-total bars, used for the division / service breakdowns. --}}
@php
    $max = max(1, max(array_column($series ?: [['value' => 0]], 'value')));
    $total = array_sum(array_column($series ?: [], 'value'));
@endphp

@if ($total === 0)
    <p class="text-sm text-slate-400 py-6 text-center">{{ $empty }}</p>
@else
    <ul class="space-y-3">
        @foreach ($series as $point)
            @php
                $width = max(2, (int) round(($point['value'] / $max) * 100));
                $share = (int) round(($point['value'] / $total) * 100);
            @endphp
            <li>
                <div class="flex items-baseline justify-between gap-3 text-sm mb-1.5">
                    <span class="text-slate-700 truncate">{{ $point['label'] }}</span>
                    <span class="text-slate-400 text-xs shrink-0">{{ $point['value'] }} · {{ $share }}%</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-moaum-blue" style="width: {{ $width }}%"></div>
                </div>
            </li>
        @endforeach
    </ul>
@endif