<?php

namespace App\Http\Requests;

use App\Enums\NewsStatus;
use App\Http\Requests\Concerns\ContentRules;
use App\Models\NewsArticle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * PRD §16 — create/edit a news article.
 *
 * Writers (edit-news) move articles between Draft and Review; anything that
 * puts an article on, or takes it off, the public site — Scheduled,
 * Published, Archived — and the featured flag need 'publish-news'.
 */
class NewsArticleRequest extends FormRequest
{
    use ContentRules;

    public const PUBLIC_STATUSES = [NewsStatus::Scheduled, NewsStatus::Published, NewsStatus::Archived];

    public function authorize(): bool
    {
        $article = $this->route('article');
        $user = $this->user();

        $allowed = $article instanceof NewsArticle
            ? $user->can('update', $article)
            : $user->can('create', NewsArticle::class);

        if (! $allowed) {
            return false;
        }

        if ($user->can('publish', NewsArticle::class)) {
            return true;
        }

        return ! $this->has('featured')
            && ! self::statusChangeNeedsPublish($article, $this->input('status'));
    }

    /** Shared with the form so it only offers statuses the user may choose. */
    public static function statusChangeNeedsPublish(?NewsArticle $article, ?string $newStatus): bool
    {
        $current = $article?->status;

        if ($current?->value === $newStatus) {
            return false;
        }

        $public = array_map(fn (NewsStatus $s) => $s->value, self::PUBLIC_STATUSES);

        return in_array($newStatus, $public, true) || in_array($current?->value, $public, true);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => $this->slugRules('news', $this->route('article')?->id),
            'news_category_id' => ['nullable', 'integer', 'exists:news_categories,id'],
            'excerpt' => ['nullable', 'string', 'max:400'],
            'content' => ['required', 'string', 'max:200000'],
            'featured_image' => $this->imageRules(),
            'tags' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::enum(NewsStatus::class)],
            'published_at' => [
                'nullable', 'date',
                Rule::requiredIf($this->input('status') === NewsStatus::Scheduled->value),
                ...($this->input('status') === NewsStatus::Scheduled->value ? ['after:now'] : []),
            ],
            'featured' => ['sometimes', 'boolean'],
            ...$this->seoRules(),
        ];
    }

    public function messages(): array
    {
        return $this->contentMessages() + [
            'content.required' => 'Write the article before saving.',
            'published_at.required' => 'Choose when the scheduled article should go live.',
            'published_at.after' => 'A scheduled article needs a date in the future.',
        ];
    }

    /** @return array<string, mixed> */
    public function articleData(): array
    {
        $data = $this->validated();
        $article = $this->route('article');

        $data['tags'] = collect(explode(',', (string) ($data['tags'] ?? '')))
            ->map(fn ($tag) => trim($tag))->filter()->unique()->values()->all() ?: null;

        $data['published_at'] = filled($data['published_at'] ?? null) ? Carbon::parse($data['published_at']) : null;

        // Publishing without a date means "now" — or keeps the original date
        // when an already-published article is re-saved.
        if ($data['status'] === NewsStatus::Published->value && $data['published_at'] === null) {
            $data['published_at'] = $article?->published_at ?? now();
        }

        return $data;
    }
}
