@props(['action', 'label' => 'Delete', 'confirm' => 'Delete this item? This cannot be undone.'])

<form method="POST" action="{{ $action }}" onsubmit="return confirm(@js($confirm))" {{ $attributes->merge(['class' => 'inline']) }}>
    @csrf
    @method('DELETE')
    <button type="submit" class="btn-ghost text-sm text-moaum-red hover:bg-red-50" @if (blank($label)) aria-label="Delete" @endif>
        <x-icon name="trash" class="w-4 h-4" /> {{ $label }}
    </button>
</form>
