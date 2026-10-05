<?php

namespace App\Http\Requests;

use App\Enums\PageStatus;
use App\Http\Requests\Concerns\ContentRules;
use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PRD §22 — create/edit a CMS page. Changing status needs 'publish-pages'.
 * A system page's slug is fixed (its route does not use it), so it is never
 * accepted for those.
 */
class PageRequest extends FormRequest
{
    use ContentRules;

    public function authorize(): bool
    {
        $page = $this->route('page');
        $user = $this->user();

        $allowed = $page instanceof Page
            ? $user->can('update', $page)
            : $user->can('create', Page::class);

        if (! $allowed) {
            return false;
        }

        $statusChanged = $this->has('status') && $this->input('status') !== $page?->status?->value;

        return ! $statusChanged || $user->can('publish', Page::class);
    }

    public function rules(): array
    {
        $page = $this->route('page');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => $page?->isSystem() ? ['prohibited'] : $this->slugRules('pages', $page?->id),
            'summary' => ['nullable', 'string', 'max:300'],
            'content' => ['nullable', 'string', 'max:200000'],
            'hero_image' => $this->imageRules(),
            'status' => ['sometimes', 'required', Rule::enum(PageStatus::class)],
            ...$this->seoRules(),
        ];
    }

    public function messages(): array
    {
        return $this->contentMessages() + [
            'slug.prohibited' => 'The address of a system page cannot be changed.',
        ];
    }
}
