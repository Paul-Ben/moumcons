<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** PRD §15 — public "register interest" form on a training programme. */
class TrainingInterestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public form, protected by rate limiting + honeypot
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'organization' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'participants' => ['required', 'integer', 'min:1', 'max:500'],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
            'website' => ['prohibited'], // honeypot
        ];
    }

    public function messages(): array
    {
        return [
            'consent.accepted' => 'You must consent to being contacted about this programme.',
        ];
    }
}
