<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use App\Http\Requests\Concerns\ContentRules;
use App\Http\Requests\Concerns\HandlesPublication;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PRD §14 — create/edit a project. Publishing and featuring need
 * 'publish-projects'.
 */
class ProjectRequest extends FormRequest
{
    use ContentRules, HandlesPublication;

    public function authorize(): bool
    {
        $project = $this->route('project');
        $user = $this->user();

        $allowed = $project instanceof Project
            ? $user->can('update', $project)
            : $user->can('create', Project::class);

        if (! $allowed) {
            return false;
        }

        return ! ($this->touchesPublication() || $this->has('featured')) || $user->can('publish', Project::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => $this->slugRules('projects', $this->route('project')?->id),
            'business_division_id' => ['nullable', 'integer', 'exists:business_divisions,id'],
            'client' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:50000'],
            'scope' => ['nullable', 'string', 'max:20000'],
            'start_date' => ['nullable', 'date'],
            'completion_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'featured_image' => $this->imageRules(),
            'featured' => ['sometimes', 'boolean'],
            'tags' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            ...$this->seoRules(),
            ...$this->publicationRules(),
            'gallery' => ['nullable', 'array', 'max:60'],
            'gallery.*.image' => ['required', ...array_slice($this->imageRules(), 1)],
            'gallery.*.caption' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return $this->contentMessages() + [
            'completion_date.after_or_equal' => 'The completion date cannot be before the start date.',
        ];
    }

    /** @return array<string, mixed> project columns */
    public function projectData(): array
    {
        $data = collect($this->validated())->except('gallery')->all();
        $data['sort_order'] ??= 0;
        $data['tags'] = collect(explode(',', (string) ($data['tags'] ?? '')))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->unique()
            ->values()
            ->all() ?: null;

        return $this->applyPublication($data, $this->route('project'));
    }

    /** @return list<array{image: string, caption: ?string}> */
    public function galleryRows(): array
    {
        return array_values(array_map(
            fn ($row) => ['image' => $row['image'], 'caption' => $row['caption'] ?? null],
            $this->validated('gallery') ?? []
        ));
    }
}
