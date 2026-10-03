@props(['item'])
<a href="{{ $item['href'] ?? '#' }}"
   class="flex items-center gap-3 px-3 py-2.5 {{ ($item['active'] ?? false) ? 'bg-moaum-blue/20 text-moaum-blue rounded-lg font-medium' : 'text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg transition' }}">
    <x-icon :name="$item['icon']" class="w-5 h-5" />
    {{ $item['label'] }}
    @if (!empty($item['count']))
        <span class="ml-auto bg-moaum-red text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $item['count'] }}</span>
    @endif
</a>
