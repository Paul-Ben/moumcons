<?php

namespace App\Http\Requests;

use App\Enums\DeliveryMode;
use App\Enums\TrainingStatus;
use App\Http\Requests\Concerns\ContentRules;
use App\Http\Requests\Concerns\HandlesPublication;
use App\Models\TrainingProgramme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** PRD §15 — create/edit a training programme. Publishing needs 'publish-training'. */
class TrainingProgrammeRequest extends FormRequest
{
    use ContentRules, HandlesPublication;

    public function authorize(): bool
    {
        $programme = $this->route('programme');
        $user = $this->user();

        $allowed = $programme instanceof TrainingProgramme
            ? $user->can('update', $programme)
            : $user->can('create', TrainingProgramme::class);

        if (! $allowed) {
            return false;
        }

        return ! ($this->touchesPublication() || $this->has('featured')) || $user->can('publish', TrainingProgramme::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => $this->slugRules('training_programmes', $this->route('programme')?->id),
            'business_division_id' => ['nullable', 'integer', 'exists:business_divisions,id'],
            'summary' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:50000'],
            'course_category' => ['nullable', 'string', 'max:255'],
            'trainer' => ['nullable', 'string', 'max:255'],
            'duration' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'registration_deadline' => ['nullable', 'date'],
            'delivery_mode' => ['required', Rule::enum(DeliveryMode::class)],
            'venue' => ['nullable', 'string', 'max:255'],
            'fee' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'curriculum' => ['nullable', 'string', 'max:50000'],
            'requirements' => ['nullable', 'string', 'max:20000'],
            'certificate_info' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(TrainingStatus::class)],
            'featured_image' => $this->imageRules(),
            'featured' => ['sometimes', 'boolean'],
            ...$this->seoRules(),
            ...$this->publicationRules(),
        ];
    }

    public function messages(): array
    {
        return $this->contentMessages() + [
            'end_date.after_or_equal' => 'The end date cannot be before the start date.',
        ];
    }

    /** @return array<string, mixed> */
    public function programmeData(): array
    {
        return $this->applyPublication($this->validated(), $this->route('programme'));
    }
}
