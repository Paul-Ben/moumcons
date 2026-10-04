{{-- Admin → Quote request detail: triage + quote preparation (PRD §13/§25/§32). --}}
@use('App\Policies\QuoteRequestPolicy')
@php
    $user = auth()->user();
    $mayUpdate = $user->can('update', $item);
    $mayPrepare = $user->can('prepare', $item);
    $mayClose = $user->can('close', $item);

    // Only offer statuses this user can actually move to, so the server's
    // refusal is a backstop rather than the normal experience.
    $allowedStatuses = collect($statuses)->filter(function ($label, $value) use ($item, $mayClose, $mayPrepare) {
        if ($item->status->value === $value) {
            return true;
        }
        if (QuoteRequestPolicy::statusTransitionRequiresClosePermission($item, $value) && ! $mayClose) {
            return false;
        }

        return ! (QuoteRequestPolicy::statusTransitionRequiresPreparePermission($item, $value) && ! $mayPrepare);
    });
@endphp

<x-layouts.admin title="Quote request {{ $item->reference }}">
    <div class="space-y-6">

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <a href="{{ route('admin.quote-requests.index') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-moaum-blue transition">
                    <x-icon name="chevron-right" class="w-4 h-4 rotate-180" /> Back to quote requests
                </a>
                <h1 class="font-display text-2xl font-bold text-moaum-charcoal mt-2 break-words">{{ $item->project_title }}</h1>
                <p class="text-sm text-slate-500 mt-1">
                    <span class="font-mono">{{ $item->reference }}</span> &middot;
                    received {{ $item->created_at->format('d M Y, H:i') }}
                    @if ($item->quote_sent_at)
                        &middot; quote sent {{ $item->quote_sent_at->format('d M Y') }}
                    @endif
                </p>
            </div>
            <span class="badge {{ $item->status->badgeClasses() }}">{{ $item->status->label() }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="card">
                    <h2 class="font-semibold text-moaum-charcoal mb-4">Scope / specifications</h2>
                    <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $item->requirements }}</p>

                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 pt-6 border-t border-slate-100 text-sm">
                        <x-admin.detail label="Location">{{ $item->location ?: '—' }}</x-admin.detail>
                        <x-admin.detail label="Quantity / size">{{ $item->estimated_quantity ?: '—' }}</x-admin.detail>
                        <x-admin.detail label="Budget range">{{ $item->budget_range ?: '—' }}</x-admin.detail>
                        <x-admin.detail label="Desired start">{{ $item->desired_start_date?->format('d M Y') ?? '—' }}</x-admin.detail>
                        <x-admin.detail label="Desired completion">{{ $item->desired_completion_date?->format('d M Y') ?? '—' }}</x-admin.detail>
                    </dl>
                </div>

                @if ($item->attachment)
                    <x-admin.attachment-card :path="$item->attachment" :href="route('admin.quote-requests.attachment', $item)" />
                @endif

                @if ($item->quoted_amount !== null || filled($item->quote_message))
                    <div class="card border-l-4 border-l-moaum-green">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <h2 class="font-semibold text-moaum-charcoal">Prepared quote</h2>
                            <span class="text-xs text-slate-400">{{ $item->quote_sent_at ? 'Visible to the customer' : 'Draft — not yet sent' }}</span>
                        </div>
                        @if ($item->quoted_amount !== null)
                            <p class="font-display text-2xl font-bold text-moaum-charcoal">₦{{ number_format((float) $item->quoted_amount, 2) }}</p>
                        @endif
                        @if (filled($item->quote_message))
                            <p class="text-sm text-slate-600 whitespace-pre-line mt-2">{{ $item->quote_message }}</p>
                        @endif
                        @if ($item->quote_valid_until)
                            <p class="text-xs text-slate-400 mt-2">Valid until {{ $item->quote_valid_until->format('d M Y') }}</p>
                        @endif
                    </div>
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

                @if ($mayUpdate || $mayPrepare)
                    <form method="POST" action="{{ route('admin.quote-requests.update', $item) }}" class="card space-y-4">
                        @csrf
                        @method('PATCH')

                        <h2 class="font-semibold text-moaum-charcoal">Manage quote</h2>

                        @if ($mayUpdate)
                            <div>
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-input text-sm">
                                    @foreach ($allowedStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected($item->status->value === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status') <p class="form-error">{{ $message }}</p> @enderror
                                <p class="mt-1 text-xs text-slate-400">
                                    Moving to <strong>Quote Sent</strong> emails the quote below to the customer.
                                </p>
                            </div>

                            <label class="flex items-start gap-2 text-sm text-slate-600">
                                <input type="hidden" name="notify_customer" value="0">
                                <input type="checkbox" name="notify_customer" value="1" @checked(old('notify_customer', true))
                                       class="mt-0.5 rounded border-slate-300 text-moaum-blue focus:ring-moaum-blue">
                                Email the customer about other status changes
                            </label>
                        @endif

                        @if ($mayPrepare)
                            <div class="pt-4 border-t border-slate-100 space-y-4">
                                <div>
                                    <label for="quoted_amount" class="form-label">Quoted amount (₦)</label>
                                    <input id="quoted_amount" type="number" name="quoted_amount" min="0" step="0.01"
                                           value="{{ old('quoted_amount', $item->quoted_amount) }}" class="form-input text-sm">
                                    @error('quoted_amount') <p class="form-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="quote_message" class="form-label">Message to the customer</label>
                                    <textarea id="quote_message" name="quote_message" rows="4" class="form-input text-sm"
                                              placeholder="What the price covers, terms, next steps…">{{ old('quote_message', $item->quote_message) }}</textarea>
                                    @error('quote_message') <p class="form-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="quote_valid_until" class="form-label">Valid until</label>
                                    <input id="quote_valid_until" type="date" name="quote_valid_until"
                                           value="{{ old('quote_valid_until', $item->quote_valid_until?->format('Y-m-d')) }}" class="form-input text-sm">
                                    @error('quote_valid_until') <p class="form-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @endif

                        @if ($mayUpdate)
                            <div class="pt-4 border-t border-slate-100 space-y-4">
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
                                    <textarea id="internal_notes" name="internal_notes" rows="4" class="form-input text-sm"
                                              placeholder="Visible to staff only…">{{ old('internal_notes', $item->internal_notes) }}</textarea>
                                    @error('internal_notes') <p class="form-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @endif

                        <button type="submit" class="btn-primary w-full">
                            <x-icon name="check-circle" class="w-4 h-4" /> Save changes
                        </button>
                    </form>
                @else
                    <div class="card text-sm text-slate-500">You have read-only access to this quote request.</div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
