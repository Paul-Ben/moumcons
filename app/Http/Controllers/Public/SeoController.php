<?php

namespace App\Http\Controllers\Public;

use App\Enums\JobStatus;
use App\Enums\ServiceStatus;
use App\Http\Controllers\Controller;
use App\Models\BusinessDivision;
use App\Models\Gallery;
use App\Models\JobOpening;
use App\Models\NewsArticle;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\TrainingProgramme;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/** PRD §34 — XML sitemap, robots.txt and the news RSS feed. */
class SeoController extends Controller
{
    public function sitemap(): Response
    {
        // Cached briefly: cheap to rebuild, and crawlers hit it repeatedly.
        $xml = Cache::remember('seo.sitemap', now()->addMinutes(30), fn () => view('seo.sitemap', ['urls' => $this->urls()])->render());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = app()->environment('production')
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /login', 'Disallow: /search', '', 'Sitemap: '.route('sitemap')]
            // Never let staging or local copies be indexed.
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function newsFeed(): Response
    {
        $articles = NewsArticle::query()->published()->with('category:id,name')->latestFirst()->take(20)->get();

        return response(view('seo.feed', ['articles' => $articles])->render(), 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }

    /** @return list<array{loc: string, lastmod: ?string}> */
    private function urls(): array
    {
        $static = ['home', 'businesses.index', 'services.index', 'projects.index', 'training.index', 'news.index',
            'careers.index', 'downloads.index', 'faqs.index', 'gallery.index', 'contact.index',
            'requests.service.create', 'requests.quote.create'];

        $urls = array_map(fn ($name) => ['loc' => route($name), 'lastmod' => null], $static);

        $add = function ($models, callable $url) use (&$urls) {
            foreach ($models as $model) {
                $urls[] = ['loc' => $url($model), 'lastmod' => $model->updated_at?->toAtomString()];
            }
        };

        $add(Page::query()->published()->get(), fn (Page $p) => $p->url());
        $add(BusinessDivision::query()->publiclyVisible()->ordered()->get(), fn ($d) => route('businesses.show', $d));
        $add(Service::query()->where('status', ServiceStatus::Active)->whereHas('division', fn ($q) => $q->publiclyVisible())->get(), fn ($s) => route('services.show', $s));
        $add(Project::query()->published()->get(), fn ($p) => route('projects.show', $p));
        $add(NewsArticle::query()->published()->get(), fn ($n) => route('news.show', $n));
        $add(TrainingProgramme::query()->published()->get(), fn ($t) => route('training.show', $t));
        $add(JobOpening::query()->published()->where('status', JobStatus::Open)->get(), fn ($j) => route('careers.show', $j));
        $add(Gallery::query()->published()->whereHas('images')->get(), fn ($g) => route('gallery.show', $g));

        return $urls;
    }
}
