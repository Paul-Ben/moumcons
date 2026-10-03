@props(['name', 'class' => 'w-5 h-5'])

{{-- Lucide-style outline icon (Design System §23). Rendered server-side. --}}
{!! \App\Support\Icons::render($name, $class) !!}
