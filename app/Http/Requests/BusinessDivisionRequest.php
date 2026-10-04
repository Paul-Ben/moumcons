<?php

namespace App\Http\Requests;

use App\Enums\DivisionStatus;
use App\Http\Requests\Concerns\ContentRules;
use App\Models\BusinessDivision;
use App\Support\Icons;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PRD §10 — create/edit a business division with its capabilities.
 *
 * Status and the featured flag decide whether and how the division appears
 * publicly, so they need 'publish-divisions'; submitting them without it is
 * refused rather than silently ignored.
 */
class BusinessDivisionRequest extends FormRequest
{
    use ContentRules;

    public function authorize(): bool
    {
        $division = $this->route('division');
        $user = $this->user();

        $allowed = $division instanceof BusinessDivision
            ? $user->can('update', $division)
            : $user->can('create', BusinessDivision::class);

        if (! $allowed) {
            return false;
        }

        return ! $this->hasAny(['status', 'featured']) || $user->can('publish', BusinessDivision::class);
    }

    public function rules(): array
    {
        $division = $this->route('division');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => $this->slugRules('business_divisions', $division?->id),
            'short_description' => ['nullable', 'string', 'max:255'],
            'full_description' => ['nullable', 'string', 'max:50000'],
            'category' => ['nullable', Rule::in(collect(config('moaum.nav.business_groups'))->pluck('heading'))],
            'status' => ['sometimes', 'required', Rule::enum(DivisionStatus::class)],
            'featured' => ['sometimes', 'boolean'],
            'icon' => ['nullable', Rule::in(Icons::names())],
            'hero_image' => $this->imageRules(),
            'cover_image' => $this->imageRules(),
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'location' => ['nullable', 'string', 'max:255'],
            'operating_hours' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            ...$this->seoRules(),

            'capabilities' => ['nullable', 'array', 'max:24'],
            'capabilities.*.id' => ['nullable', 'integer'],
            'capabilities.*.title' => ['required_with:capabilities.*.description', 'nullable', 'string', 'max:255'],
            'capabilities.*.description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return $this->contentMessages() + [
            'capabilities.*.title.required_with' => 'Each capability needs a title.',
        ];
    }

    /** @return array<string, mixed> division columns */
    public function divisionData(): array
    {
        $data = collect($this->validated())->except('capabilities')->all();
        $data['sort_order'] ??= 0;

        return $data;
    }

    /** @return list<array{id: ?int, title: string, description: ?string}> non-empty rows, in order */
    public function capabilityRows(): array
    {
        return collect($this->validated('capabilities') ?? [])
            ->filter(fn ($row) => filled($row['title'] ?? null))
            ->map(fn ($row) => [
                'id' => isset($row['id']) ? (int) $row['id'] : null,
                'title' => $row['title'],
                'description' => $row['description'] ?? null,
            ])
            ->values()
            ->all();
    }
}
