<?php

namespace App\Http\Requests;

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PRD §12 — admin triage of a service request (status, ownership, notes).
 * Every triage field needs 'update-service-requests' (via the policy).
 */
class UpdateServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $request = $this->route('serviceRequest');

        return $request instanceof ServiceRequest
            && $this->user()?->can('update', $request) === true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', Rule::enum(RequestStatus::class)],
            // Only active staff can own a request.
            'assigned_to' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'internal_notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'notify_customer' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_to.exists' => 'That user cannot be assigned — choose an active member of staff.',
            'internal_notes.max' => 'Internal notes are limited to 5000 characters.',
            'status.enum' => 'That is not a valid request status.',
        ];
    }

    /** @return array<string, mixed> columns to persist */
    public function triagePayload(): array
    {
        return collect($this->validated())->except('notify_customer')->all();
    }

    public function shouldNotifyCustomer(): bool
    {
        return $this->boolean('notify_customer');
    }
}
