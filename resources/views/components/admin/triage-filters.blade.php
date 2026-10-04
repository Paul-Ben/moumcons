@props(['action', 'filters', 'statuses', 'divisions', 'staff', 'placeholder' => 'Search…'])

{{-- Filter bar shared by the service-request and quote-request queues. --}}
<form method="GET" action="{{ $action }}" class="card p-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="lg:col-span-2">
            <label for="q" class="form-label sr-only">Search</label>
            <div class="relative">
                <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="{{ $placeholder }}" class="form-input pl-9 text-sm">
            </div>
        </div>

        <div>
            <label for="status" class="form-label sr-only">Status</label>
            <select id="status" name="status" class="form-input text-sm">
                <option value="">All statuses</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="division" class="form-label sr-only">Division</label>
            <select id="division" name="division" class="form-input text-sm">
                <option value="">All divisions</option>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" @selected((int) ($filters['division'] ?? 0) === $division->id)>{{ $division->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="assigned" class="form-label sr-only">Owner</label>
            <select id="assigned" name="assigned" class="form-input text-sm">
                <option value="">Anyone</option>
                @foreach ($staff as $member)
                    <option value="{{ $member->id }}" @selected((int) ($filters['assigned'] ?? 0) === $member->id)>{{ $member->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-input text-sm" aria-label="Received from">
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-input text-sm" aria-label="Received to">
        </div>

        <div class="flex items-center">
            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="unassigned" value="1" @checked($filters['unassigned'] ?? false)
                       class="rounded border-slate-300 text-moaum-blue focus:ring-moaum-blue">
                Unassigned only
            </label>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3 mt-4">
        <button type="submit" class="btn-primary text-sm inline-flex items-center gap-2">
            <x-icon name="sliders-horizontal" class="w-4 h-4" /> Apply filters
        </button>
        @if (collect($filters)->filter(fn ($v) => filled($v))->isNotEmpty())
            <a href="{{ $action }}" class="btn-ghost text-sm">Clear</a>
        @endif
    </div>
</form>
