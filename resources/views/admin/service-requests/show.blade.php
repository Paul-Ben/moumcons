{{-- Admin → Service request detail + triage panel (PRD §12/§25/§32). --}}
<x-layouts.admin title="Service request {{ $item->reference }}">
    <div class="space-y-6">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <a href="{{ route('admin.service-requests.index') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-moaum-blue transition">
                    <x-icon name="chevron-right" class="w-4 h-4 rotate-180" /> Back to service requests
                </a>
                <h1 class="font-display text-2xl font-bold text-moaum-charcoal mt-2 break-words">
                    {{ $item->service?->name ?? $item->division?->name ?? 'Service request' }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    <span class="font-mono">{{ $item->reference }}</span> &middot;
                    received {{ $item->created_at->format('d M Y, H:i') }}
                </p>
            </div>
            <span class="badge {{ $item->status->badgeClasses() }}">{{ $item->status->label() }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="card">
                    <h2 class="font-semibold text-moaum-charcoal mb-4">Requirements</h2>
                    <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $item->requirements }}</p>

                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 pt-6 border-t border-slate-100 text-sm">
                        <x-admin.detail label="Location">{{ $item->location ?: '—' }}</x-admin.detail>
                        <x-admin.detail label="Preferred date">{{ $item->preferred_date?->format('d M Y') ?? '—' }}</x-admin.detail>
                        <x-admin.detail label="Budget range">{{ $item->budget_range ?: '—' }}</x-admin.detail>
                    </dl>
                </div>

                @if ($item->attachment)
                    <x-admin.attachment-card :path="$item->attachment" :href="route('admin.service-requests.attachment', $item)" />
                @endif

                @if (filled($item->internal_notes))
                    <div class="card border-l-4 border-l-moaum-blue">
                        <h2 class="font-semibold text-moaum-charcoal mb-2">Internal notes</h2>
                        <p class="text-sm text-slate-600 whitespace-pre-line">{{ $item->internal_notes }}</p>
                    </div>
                @endif

                <x-admin.activity-trail :trail="$trail" />
            </div>

            <div class="space-y-6">
                <div class="card">
                    <h2 class="font-semibold text-moaum-charcoal mb-4">Requester</h2>
                    <dl class="space-y-3 text-sm">
                        <x-admin.detail label="Name"><span class="text-moaum-charcoal font-medium">{{ $item->name }}</span></x-admin.detail>
                        @if ($item->organization)
                            <x-admin.detail label="Organisation">{{ $item->organization }}</x-admin.detail>
                        @endif
                        <x-admin.detail label="Email">
                            <a href="mailto:{{ $item->email }}" class="text-moaum-blue hover:underline break-all">{{ $item->email }}</a>
                        </x-admin.detail>
                        <x-admin.detail label="Phone">{{ $item->phone }}</x-admin.detail>
                        <x-admin.detail label="Division">{{ $item->division?->name ?? '—' }}</x-admin.detail>
                        @if ($item->service)
                            <x-admin.detail label="Service">{{ $item->service->name }}</x-admin.detail>
                        @endif
                        <x-admin.detail label="Consent">{{ $item->consent ? 'Given' : 'Not recorded' }}</x-admin.detail>
                    </dl>
                </div>

                @can('update', $item)
                    <form method="POST" action="{{ route('admin.service-requests.update', $item) }}" class="card space-y-4">
                        @csrf
                        @method('PATCH')

                        <h2 class="font-semibold text-moaum-charcoal">Triage</h2>

                        <div>
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-input text-sm">
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected($item->status->value === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <label class="flex items-start gap-2 text-sm text-slate-600">
                            <input type="hidden" name="notify_customer" value="0">
                            <input type="checkbox" name="notify_customer" value="1" @checked(old('notify_customer', true))
                                   class="mt-0.5 rounded border-slate-300 text-moaum-blue focus:ring-moaum-blue">
                            Email the customer if the status changes
                        </label>

                        <div>
                            <label for="assigned_to" class="form-label">Owner</label>
                            <select id="assigned_to" name="assigned_to" class="form-input text-sm">
                                <option value="">Unassigned</option>
                                @foreach ($staff as $member)
                                    <option value="{{ $member->id }}" @selected($item->assigned_to === $member->id)>{{ $member->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="internal_notes" class="form-label">Internal notes</label>
                            <textarea id="internal_notes" name="internal_notes" rows="5" class="form-input text-sm"
                                      placeholder="Visible to staff only…">{{ old('internal_notes', $item->internal_notes) }}</textarea>
                            @error('internal_notes') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="btn-primary w-full">
                            <x-icon name="check-circle" class="w-4 h-4" /> Save changes
                        </button>
                    </form>
                @else
                    <div class="card text-sm text-slate-500">You have read-only access to this request.</div>
                @endcan
            </div>
        </div>
    </div>
</x-layouts.admin>
