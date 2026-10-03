<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** PRD §12 — Request-a-Service (Flow A) validation. */
class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public form, protected by rate limiting
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
            'location' => ['nullable', 'string', 'max:150'],
            'preferred_date' => ['nullable', 'date'],
            'requirements' => ['required', 'string', 'min:20', 'max:10000'],
            'budget_range' => ['nullable', 'string', 'max:60'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip', 'max:10240'], // 10 MB
            'consent' => ['accepted'],
            'website' => ['prohibited'], // honeypot
        ];
    }

    public function messages(): array
    {
        return [
            'requirements.min' => 'Please describe your requirements in a little more detail (at least 20 characters).',
            'consent.accepted' => 'You must consent to being contacted before submitting this request.',
            'attachment.max' => 'The attachment must not exceed 10 MB.',
        ];
    }
}
