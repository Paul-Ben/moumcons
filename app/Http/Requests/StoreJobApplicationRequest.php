<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** PRD §17 — public job application form. */
class StoreJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public form, protected by rate limiting + honeypot
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:30'],
            'qualifications' => ['nullable', 'string', 'max:3000'],
            'cover_letter' => ['nullable', 'string', 'max:10000'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // 5 MB
            'consent' => ['accepted'],
            'website' => ['prohibited'], // honeypot
        ];
    }

    public function messages(): array
    {
        return [
            'cv.required' => 'Please attach your CV.',
            'cv.mimes' => 'Your CV must be a PDF or Word document.',
            'cv.max' => 'Your CV must be 5 MB or smaller.',
            'consent.accepted' => 'Please agree to us processing your application.',
        ];
    }
}
