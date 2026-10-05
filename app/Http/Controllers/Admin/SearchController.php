<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessDivision;
use App\Models\Enquiry;
use App\Models\JobApplication;
use App\Models\NewsArticle;
use App\Models\Page;
use App\Models\Project;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\TrainingProgramme;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin top-bar search: one box for references, people and content. Each
 * group is included only when the user may open that area, so results never
 * leak records the screens themselves would refuse.
 */
class SearchController extends Controller
{
    private const LIMIT = 8;

    public function __invoke(Request $request): View
    {
        $term = trim((string) ($request->validate(['q' => ['nullable', 'string', 'max:100']])['q'] ?? ''));
        $user = $request->user();
        $groups = [];

        if (mb_strlen($term) >= 2) {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
            $match = fn (array $columns) => fn (Builder $q) => $q->where(function (Builder $inner) use ($columns, $like) {
                foreach ($columns as $column) {
                    $inner->orWhere($column, 'like', $like);
                }
            });
            $people = ['reference', 'name', 'email', 'organization'];

            $candidates = [
                ['Enquiries', 'view-enquiries', fn () => Enquiry::query()->where($match([...$people, 'subject']))->latest()->limit(self::LIMIT)->get()
                    ->map(fn ($e) => [$e->reference.' · '.$e->name, $e->subject, route('admin.enquiries.show', $e), $e->status->label()])],
                ['Service requests', 'view-service-requests', fn () => ServiceRequest::query()->where($match($people))->latest()->limit(self::LIMIT)->get()
                    ->map(fn ($r) => [$r->reference.' · '.$r->name, $r->location, route('admin.service-requests.show', $r), $r->status->label()])],
                ['Quote requests', 'view-quotes', fn () => QuoteRequest::query()->where($match([...$people, 'project_title']))->latest()->limit(self::LIMIT)->get()
                    ->map(fn ($q) => [$q->reference.' · '.$q->name, $q->project_title, route('admin.quote-requests.show', $q), $q->status->label()])],
                ['Applications', 'view-applications', fn () => JobApplication::query()->where($match(['reference', 'name', 'email']))->latest()->limit(self::LIMIT)->get()
                    ->map(fn ($a) => [$a->reference.' · '.$a->name, $a->email, route('admin.applications.show', $a), $a->status->label()])],
                ['Users', 'manage-users', fn () => User::query()->where($match(['name', 'email']))->orderBy('name')->limit(self::LIMIT)->get()
                    ->map(fn ($u) => [$u->name, $u->email, route('admin.users.edit', $u), $u->is_active ? 'Active' : 'Deactivated'])],
                ['Business divisions', 'view-divisions', fn () => BusinessDivision::query()->where($match(['name']))->ordered()->limit(self::LIMIT)->get()
                    ->map(fn ($d) => [$d->name, $d->category, route('admin.divisions.edit', $d), $d->status->label()])],
                ['Services', 'view-services', fn () => Service::query()->where($match(['name']))->ordered()->limit(self::LIMIT)->get()
                    ->map(fn ($s) => [$s->name, null, route('admin.services.edit', $s), $s->status->label()])],
                ['Projects', 'view-projects', fn () => Project::query()->where($match(['title', 'client']))->latest()->limit(self::LIMIT)->get()
                    ->map(fn ($p) => [$p->title, $p->client, route('admin.projects.edit', $p), $p->publicationLabel()])],
                ['News', 'view-news', fn () => NewsArticle::query()->where($match(['title']))->latest()->limit(self::LIMIT)->get()
                    ->map(fn ($n) => [$n->title, null, route('admin.news.edit', $n), $n->status->label()])],
                ['Training', 'view-training', fn () => TrainingProgramme::query()->where($match(['title']))->latest()->limit(self::LIMIT)->get()
                    ->map(fn ($t) => [$t->title, $t->dateRange(), route('admin.training.edit', $t), $t->status->label()])],
                ['Pages', 'view-pages', fn () => Page::query()->where($match(['title']))->orderBy('title')->limit(self::LIMIT)->get()
                    ->map(fn ($p) => [$p->title, null, route('admin.pages.edit', $p), $p->status->label()])],
            ];

            foreach ($candidates as [$label, $permission, $query]) {
                if ($user->can($permission) && ($rows = $query())->isNotEmpty()) {
                    $groups[$label] = $rows->map(fn ($row) => array_combine(['title', 'detail', 'url', 'status'], $row));
                }
            }
        }

        return view('admin.search', ['term' => $term, 'groups' => $groups]);
    }
}
