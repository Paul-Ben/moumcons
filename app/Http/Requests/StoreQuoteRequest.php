<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** PRD §13 — Request-a-Quote (Flow B) validation. */
class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'organization' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:30'],
            'business_division_id' => ['required', 'integer', 'exists:business_divisions,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'project_title' => ['required', 'string', 'max:190'],
            'location' => ['nullable', 'string', 'max:150'],
            'requirements' => ['required', 'string', 'min:20', 'max:10000'],
            'estimated_quantity' => ['nullable', 'string', 'max:60'],
            'desired_start_date' => ['nullable', 'date'],
            'desired_completion_date' => ['nullable', 'date', 'after_or_equal:desired_start_date'],
            'budget_range' => ['nullable', 'string', 'max:60'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip', 'max:10240'],
            'consent' => ['accepted'],
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'desired_completion_date.after_or_equal' => 'The completion date must be on or after the start date.',
            'requirements.min' => 'Please describe the project scope in a little more detail (at least 20 characters).',
            'consent.accepted' => 'You must consent to being contacted before submitting this request.',
        ];
    }
}
