{{-- Admin → Enquiries: single enquiry + triage panel and audit trail (PRD §21/§32). --}}
<x-layouts.admin title="Enquiry {{ $enquiry->reference }}">
    <div class="space-y-6">

        {{-- Page header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <a href="{{ route('admin.enquiries.index') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-moaum-blue transition">
                    <x-icon name="chevron-right" class="w-4 h-4 rotate-180" /> Back to enquiries
                </a>
                <h1 class="font-display text-2xl font-bold text-moaum-charcoal mt-2 break-words">
                    {{ $enquiry->subject }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    <span class="font-mono">{{ $enquiry->reference }}</span> &middot;
                    received {{ $enquiry->created_at->format('d M Y, H:i') }}
                    @if ($enquiry->resolved_at)
                        &middot; resolved {{ $enquiry->resolved_at->format('d M Y') }}
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge {{ $enquiry->priority->badgeClasses() }}">{{ $enquiry->priority->label() }}</span>
                <span class="badge {{ $enquiry->status->badgeClasses() }}">{{ $enquiry->status->label() }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Enquiry content --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="card">
                    <h2 class="font-semibold text-moaum-charcoal mb-4">Message</h2>
                    <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $enquiry->message }}</p>
                </div>

                @if ($enquiry->attachment)
                    <x-admin.attachment-card :path="$enquiry->attachment" :href="route('admin.enquiries.attachment', $enquiry)" />
                @endif

                {{-- Internal notes are staff-only; never surfaced to the customer. --}}
                @if (filled($enquiry->internal_notes))
                    <div class="card border-l-4 border-l-moaum-blue">
                        <h2 class="font-semibold text-moaum-charcoal mb-2">Internal notes</h2>
                        <p class="text-sm text-slate-600 whitespace-pre-line">{{ $enquiry->internal_notes }}</p>
                    </div>
                @endif

                <x-admin.activity-trail :trail="$trail" />
            </div>

            {{-- Triage --}}
            <div class="space-y-6">
                <div class="card">
                    <h2 class="font-semibold text-moaum-charcoal mb-4">Requester</h2>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Name</dt>
                            <dd class="mt-0.5 text-moaum-charcoal font-medium">{{ $enquiry->name }}</dd>
                        </div>
                        @if ($enquiry->organization)
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Organisation</dt>
                                <dd class="mt-0.5 text-slate-600">{{ $enquiry->organization }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email</dt>
                            <dd class="mt-0.5">
                                <a href="mailto:{{ $enquiry->email }}" class="text-moaum-blue hover:underline break-all">
                                    {{ $enquiry->email }}
                                </a>
                            </dd>
                        </div>
                        @if ($enquiry->phone)
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Phone</dt>
                                <dd class="mt-0.5 text-slate-600">{{ $enquiry->phone }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Division</dt>
                            <dd class="mt-0.5 text-slate-600">{{ $enquiry->division?->name ?? 'General enquiry' }}</dd>
                        </div>
                        @if ($enquiry->service)
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Service</dt>
                                <dd class="mt-0.5 text-slate-600">{{ $enquiry->service->name }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Consent</dt>
                            <dd class="mt-0.5 text-slate-600">
                                {{ $enquiry->consent ? 'Given' : 'Not recorded' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                @php
                    $mayUpdate = auth()->user()->can('update', $enquiry);
                    $mayAssign = auth()->user()->can('assign', $enquiry);
                    $mayClose = auth()->user()->can('close', $enquiry);
                @endphp

                @if ($mayUpdate || $mayAssign)
                    <form method="POST" action="{{ route('admin.enquiries.update', $enquiry) }}" class="card space-y-4">
                        @csrf
                        @method('PATCH')

                        <h2 class="font-semibold text-moaum-charcoal">Triage</h2>

                        @if ($mayUpdate)
                            <div>
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-input text-sm">
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}" @selected($enquiry->status->value === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status') <p class="form-error">{{ $message }}</p> @enderror
                                @unless ($mayClose)
                                    <p class="mt-1 text-xs text-slate-400">
                                        Resolving or closing this enquiry needs the close-enquiries permission.
                                    </p>
                                @endunless
                            </div>

                            <div>
                                <label for="priority" class="form-label">Priority</label>
                                <select id="priority" name="priority" class="form-input text-sm">
                                    @foreach ($priorities as $value => $label)
                                        <option value="{{ $value }}" @selected($enquiry->priority->value === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('priority') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        @if ($mayAssign)
                            <div>
                                <label for="assigned_to" class="form-label">Owner</label>
                                <select id="assigned_to" name="assigned_to" class="form-input text-sm">
                                    <option value="">Unassigned</option>
                                    @foreach ($staff as $member)
                                        <option value="{{ $member->id }}" @selected($enquiry->assigned_to === $member->id)>
                                            {{ $member->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        @if ($mayUpdate)
                            <div>
                                <label for="internal_notes" class="form-label">Internal notes</label>
                                <textarea id="internal_notes" name="internal_notes" rows="5"
                                          class="form-input text-sm" placeholder="Visible to staff only…">{{ $enquiry->internal_notes }}</textarea>
                                @error('internal_notes') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                        @endif

                        <button type="submit" class="btn-primary w-full inline-flex items-center justify-center gap-2">
                            <x-icon name="check-circle" class="w-4 h-4" /> Save changes
                        </button>
                    </form>
                @else
                    <div class="card text-sm text-slate-500">
                        You have read-only access to this enquiry.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>