@props(['html'])

{{-- CMS rich text on public pages (sanitised by App\Support\RichText::render). --}}
<div {{ $attributes->merge(['class' => 'prose-moaum']) }}>{!! \App\Support\RichText::render($html) !!}</div>
