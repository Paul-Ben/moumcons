<?php

namespace App\Http\Requests;

use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use App\Http\Requests\Concerns\ContentRules;
use App\Http\Requests\Concerns\HandlesPublication;
use App\Models\JobOpening;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** PRD §17 — create/edit a job opening. Publishing needs 'publish-careers'. */
class JobOpeningRequest extends FormRequest
{
    use ContentRules, HandlesPublication;

    public function authorize(): bool
    {
        $job = $this->route('job');
        $user = $this->user();

        $allowed = $job instanceof JobOpening
            ? $user->can('update', $job)
            : $user->can('create', JobOpening::class);

        return $allowed && (! $this->touchesPublication() || $user->can('publish', JobOpening::class));
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => $this->slugRules('careers', $this->route('job')?->id),
            'business_division_id' => ['nullable', 'integer', 'exists:business_divisions,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', Rule::enum(EmploymentType::class)],
            'summary' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:50000'],
            'responsibilities' => ['nullable', 'string', 'max:50000'],
            'qualifications' => ['nullable', 'string', 'max:50000'],
            'requirements' => ['nullable', 'string', 'max:50000'],
            'application_deadline' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(JobStatus::class)],
            ...$this->publicationRules(),
        ];
    }

    public function messages(): array
    {
        return $this->contentMessages();
    }

    /** @return array<string, mixed> */
    public function jobData(): array
    {
        return $this->applyPublication($this->validated(), $this->route('job'));
    }
}
