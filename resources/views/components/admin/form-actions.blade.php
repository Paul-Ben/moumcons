@props(['cancel', 'submit' => 'Save'])

<div class="flex flex-wrap items-center justify-end gap-3">
    <a href="{{ $cancel }}" class="btn-ghost text-sm">Cancel</a>
    <button type="submit" class="btn-primary text-sm">
        <x-icon name="check-circle" class="w-4 h-4" /> {{ $submit }}
    </button>
</div>
