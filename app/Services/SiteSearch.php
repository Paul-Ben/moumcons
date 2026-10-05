<?php

namespace App\Services;

use App\Enums\JobStatus;
use App\Enums\ServiceStatus;
use App\Models\BusinessDivision;
use App\Models\Document;
use App\Models\Faq;
use App\Models\JobOpening;
use App\Models\NewsArticle;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\TrainingProgramme;
use App\Support\RichText;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * PRD §37 — global search across public content. Plain LIKE queries (the PRD
 * allows database search for the MVP); every type applies the same visibility
 * rules as its own listing page, so search never reveals unpublished content.
 */
class SiteSearch
{
    private const PER_TYPE = 6;

    /**
     * @return Collection<string, array{label: string, results: Collection<int, array{title: string, url: string, excerpt: string, meta: ?string}>}>
     */
    public function search(string $term, ?Authenticatable $user = null): Collection
    {
        $term = trim($term);

        if (mb_strlen($term) < 2) {
            return collect();
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
        $match = fn (array $columns) => fn (Builder $q) => $q->where(function (Builder $inner) use ($columns, $like) {
            foreach ($columns as $column) {
                $inner->orWhere($column, 'like', $like);
            }
        });

        $groups = [
            'divisions' => ['Business divisions', BusinessDivision::query()->publiclyVisible()->where($match(['name', 'short_description', 'full_description']))->ordered()
                ->limit(self::PER_TYPE)->get()->map(fn ($d) => $this->row($d->name, route('businesses.show', $d), $d->short_description ?: $d->full_description, $d->status->label()))],
            'services' => ['Services', Service::query()->where('status', ServiceStatus::Active)->whereHas('division', fn ($q) => $q->publiclyVisible())
                ->where($match(['name', 'short_description', 'description']))->with('division:id,name')->ordered()
                ->limit(self::PER_TYPE)->get()->map(fn ($s) => $this->row($s->name, route('services.show', $s), $s->short_description ?: $s->description, $s->division?->name))],
            'projects' => ['Projects', Project::query()->published()->where($match(['title', 'summary', 'description', 'client', 'location']))->ordered()
                ->limit(self::PER_TYPE)->get()->map(fn ($p) => $this->row($p->title, route('projects.show', $p), $p->summary ?: $p->description, $p->status->label()))],
            'news' => ['News', NewsArticle::query()->published()->where($match(['title', 'excerpt', 'content']))->latestFirst()
                ->limit(self::PER_TYPE)->get()->map(fn ($n) => $this->row($n->title, route('news.show', $n), $n->summary(), $n->published_at->format('j M Y')))],
            'training' => ['Training', TrainingProgramme::query()->published()->where($match(['title', 'summary', 'description', 'course_category']))->chronological()
                ->limit(self::PER_TYPE)->get()->map(fn ($t) => $this->row($t->title, route('training.show', $t), $t->summary ?: $t->description, $t->dateRange()))],
            'faqs' => ['FAQs', Faq::query()->published()->where($match(['question', 'answer']))->ordered()
                ->limit(self::PER_TYPE)->get()->map(fn ($f) => $this->row($f->question, route('faqs.index').'#faq-'.$f->id, $f->answer, null))],
            'downloads' => ['Downloads', Document::query()->published()->listableFor($user)->where($match(['title', 'description']))->orderBy('title')
                ->limit(self::PER_TYPE)->get()->map(fn ($d) => $this->row($d->title, route('downloads.index', ['category' => $d->category->value]), $d->description, $d->fileType()))],
            'careers' => ['Careers', JobOpening::query()->published()->where('status', JobStatus::Open)->where($match(['title', 'summary', 'description']))->latest()
                ->limit(self::PER_TYPE)->get()->map(fn ($j) => $this->row($j->title, route('careers.show', $j), $j->summary ?: $j->description, $j->employment_type->label()))],
            'pages' => ['Pages', Page::query()->published()->where($match(['title', 'summary', 'content']))->orderBy('title')
                ->limit(self::PER_TYPE)->get()->map(fn ($p) => $this->row($p->title, $p->url(), RichText::withoutPlaceholders($p->summary ?: $p->content), null))],
        ];

        return collect($groups)
            ->map(fn ($group) => ['label' => $group[0], 'results' => $group[1]])
            ->filter(fn ($group) => $group['results']->isNotEmpty());
    }

    /** @return array{title: string, url: string, excerpt: string, meta: ?string} */
    private function row(string $title, string $url, ?string $text, ?string $meta): array
    {
        return [
            'title' => $title,
            'url' => $url,
            'excerpt' => Str::limit(RichText::toPlainText($text), 180),
            'meta' => $meta,
        ];
    }
}
