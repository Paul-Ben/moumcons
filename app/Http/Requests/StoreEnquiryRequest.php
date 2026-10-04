<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** PRD §21 — Contact / enquiry form validation. */
class StoreEnquiryRequest extends FormRequest
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
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:190'],
            // Both are optional for a general enquiry, which may not relate to a
            // specific division or service.
            'business_division_id' => ['nullable', 'integer', 'exists:business_divisions,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'message' => ['required', 'string', 'min:20', 'max:10000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip', 'max:10240'], // 10 MB
            'consent' => ['accepted'],
            'website' => ['prohibited'], // honeypot
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Please give your enquiry a subject.',
            'message.min' => 'Please tell us a little more (at least 20 characters).',
            'consent.accepted' => 'You must consent to being contacted before sending this enquiry.',
            'attachment.max' => 'The attachment must not exceed 10 MB.',
        ];
    }
}
