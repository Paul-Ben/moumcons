@props([
    'series' => [],
    'height' => 160,
    'empty' => 'No activity in this period',
])

{{--
    Dependency-free column chart (PRD §24). No charting library is installed, and
    the dataset is a dozen integers, so plain flex columns beat pulling in a JS
    chart runtime. Bars are scaled against the tallest column.
--}}
@php
    $max = max(1, max(array_column($series ?: [['value' => 0]], 'value')));
    $total = array_sum(array_column($series ?: [], 'value'));
@endphp

<div>
    @if ($total === 0)
        <div class="flex items-center justify-center text-sm text-slate-400 border border-dashed border-slate-200 rounded-lg"
             style="height: {{ $height }}px">
            {{ $empty }}
        </div>
    @else
        <div class="flex items-end gap-1.5" style="height: {{ $height }}px">
            @foreach ($series as $point)
                @php $barHeight = max(3, (int) round(($point['value'] / $max) * ($height - 28))); @endphp
                <div class="flex-1 flex flex-col items-center justify-end gap-2 min-w-0"
                     title="{{ $point['hint'] }}: {{ $point['value'] }}">
                    <div class="w-full rounded-t bg-moaum-blue/85 hover:bg-moaum-blue transition-colors"
                         style="height: {{ $barHeight }}px"></div>
                    <span class="text-[10px] text-slate-400 leading-none">{{ $point['label'] }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>