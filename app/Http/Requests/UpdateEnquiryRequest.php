<?php

namespace App\Http\Requests;

use App\Enums\EnquiryStatus;
use App\Enums\Priority;
use App\Models\Enquiry;
use App\Policies\EnquiryPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PRD §21 — admin triage of an enquiry (status, priority, ownership, notes).
 *
 * Authorization is enforced per *field* rather than per screen: ownership needs
 * 'assign-enquiries', progression and notes need 'update-enquiries', and moving
 * an enquiry into or out of a closing status additionally needs
 * 'close-enquiries' (PRD §23). A user who cannot change a field never has it
 * rendered, and if it arrives anyway the request is refused.
 */
class UpdateEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $enquiry = $this->route('enquiry');

        if ($user === null || ! $enquiry instanceof Enquiry) {
            return false;
        }

        // No triage rights at all: nothing may be submitted.
        if ($user->cannot('update', $enquiry) && $user->cannot('assign', $enquiry)) {
            return false;
        }

        if ($this->filled('assigned_to') && $user->cannot('assign', $enquiry)) {
            return false;
        }

        if ($this->filled('internal_notes') && $user->cannot('update', $enquiry)) {
            return false;
        }

        if ($this->filled('status')) {
            if ($user->cannot('update', $enquiry)) {
                return false;
            }

            if (EnquiryPolicy::statusTransitionRequiresClosePermission($enquiry, $this->input('status'))
                && $user->cannot('close', $enquiry)) {
                return false;
            }
        }

        if ($this->filled('priority') && $user->cannot('update', $enquiry)) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', Rule::enum(EnquiryStatus::class)],
            'priority' => ['sometimes', 'required', Rule::enum(Priority::class)],
            // Only active staff can own an enquiry; a deactivated account must not
            // remain on the hook for follow-up.
            'assigned_to' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'internal_notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_to.exists' => 'That user cannot be assigned — choose an active member of staff.',
            'internal_notes.max' => 'Internal notes are limited to 5000 characters.',
            'status.enum' => 'That is not a valid enquiry status.',
            'priority.enum' => 'That is not a valid priority.',
        ];
    }

    /**
     * Only fields this user is actually allowed to change are persisted, so a
     * permitted request can never smuggle an unauthorised column through.
     *
     * @return array<string, mixed>
     */
    public function triagePayload(): array
    {
        $payload = $this->validated();
        $enquiry = $this->route('enquiry');
        $user = $this->user();

        if (! $user->can('update', $enquiry)) {
            unset($payload['status'], $payload['priority'], $payload['internal_notes']);
        }

        if (! $user->can('assign', $enquiry)) {
            unset($payload['assigned_to']);
        }

        return $payload;
    }
}
