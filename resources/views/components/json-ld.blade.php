@props(['data'])

{{-- PRD §34 structured data. JSON_HEX_TAG keeps "</script>" in content from breaking out. --}}
@push('meta')
    <script type="application/ld+json">{!! json_encode(array_filter($data, fn ($v) => $v !== null && $v !== []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush
