<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DivisionStatus;
use App\Enums\EnquiryStatus;
use App\Enums\ServiceStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BusinessDivision;
use App\Models\Enquiry;
use App\Models\NewsArticle;
use App\Models\Project;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin → Dashboard (PRD §24).
 *
 * Read-only roll-up of triage queues and catalogue size. Every figure is
 * counted in SQL so the screen stays cheap; the module writes nothing.
 */
class DashboardController extends Controller
{
    private const TREND_MONTHS = 12;

    private const BREAKDOWN_LIMIT = 6;

    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => $this->stats(),
            'enquiriesByMonth' => $this->enquiriesByMonth(),
            'requestsByDivision' => $this->requestsByDimension('division'),
            'requestsByService' => $this->requestsByDimension('service'),
            'recentEnquiries' => Enquiry::query()
                ->with('division:id,name')
                ->latest()
                ->limit(6)
                ->get(),
            'recentRequests' => ServiceRequest::query()
                ->with('division:id,name')
                ->latest()
                ->limit(6)
                ->get(),
            'recentAudit' => AuditLog::query()
                ->with('user:id,name')
                ->latest()
                ->limit(8)
                ->get(),
            'pendingModules' => [
                ['label' => 'Training programmes', 'module' => 'Module 9'],
            ],
        ]);
    }

    /**
     * PRD §24 widget row. Only entities that exist today are counted — the
     * not-yet-built ones are listed separately rather than shown as a fake zero.
     *
     * @return array<int, array<string, mixed>>
     */
    private function stats(): array
    {
        $newEnquiries = Enquiry::query()->where('status', EnquiryStatus::New)->count();
        $actionableQuotes = QuoteRequest::query()->actionable()->count();

        return [
            [
                'label' => 'Total enquiries',
                'value' => Enquiry::query()->count(),
                'hint' => $newEnquiries > 0 ? $newEnquiries.' awaiting first response' : 'all triaged',
                'icon' => 'inbox',
                'tone' => 'blue',
            ],
            [
                'label' => 'New enquiries',
                'value' => $newEnquiries,
                'hint' => 'unopened',
                'icon' => 'zap',
                'tone' => 'red',
            ],
            [
                'label' => 'Open service requests',
                'value' => ServiceRequest::query()->open()->count(),
                'hint' => 'in triage',
                'icon' => 'briefcase',
                'tone' => 'green',
            ],
            [
                'label' => 'Quote requests',
                'value' => QuoteRequest::query()->count(),
                'hint' => $actionableQuotes > 0 ? $actionableQuotes.' awaiting decision' : 'none pending',
                'icon' => 'file-text',
                'tone' => 'charcoal',
            ],
            [
                'label' => 'Active divisions',
                'value' => BusinessDivision::query()->where('status', DivisionStatus::Active)->count(),
                'hint' => 'of '.BusinessDivision::query()->count().' total',
                'icon' => 'building',
                'tone' => 'blue',
            ],
            [
                'label' => 'Active services',
                'value' => Service::query()->where('status', ServiceStatus::Active)->count(),
                'hint' => 'of '.Service::query()->count().' total',
                'icon' => 'check-circle',
                'tone' => 'green',
            ],
            [
                'label' => 'Projects',
                'value' => Project::query()->count(),
                'hint' => Project::query()->published()->count().' published',
                'icon' => 'hard-hat',
                'tone' => 'red',
            ],
            [
                'label' => 'News articles',
                'value' => NewsArticle::query()->published()->count(),
                'hint' => NewsArticle::query()->where('status', 'review')->count().' awaiting review',
                'icon' => 'newspaper',
                'tone' => 'blue',
            ],
            [
                'label' => 'Staff users',
                'value' => User::query()->count(),
                'hint' => 'with admin access',
                'icon' => 'users',
                'tone' => 'charcoal',
            ],
        ];
    }

    /**
     * Enquiries received per month for the last 12 months (PRD §24 chart).
     *
     * Grouped in PHP rather than SQL so the query stays portable across the
     * SQLite/MySQL/Postgres drivers; 12 months of rows is trivial to aggregate.
     *
     * @return array<int, array<string, mixed>>
     */
    private function enquiriesByMonth(): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths(self::TREND_MONTHS - 1);

        $counts = Enquiry::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn (Enquiry $enquiry) => $enquiry->created_at->format('Y-m'))
            ->map(fn (Collection $group) => $group->count());

        $series = [];

        for ($i = 0; $i < self::TREND_MONTHS; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $series[] = [
                'label' => $month->format('M'),
                'hint' => $month->format('F Y'),
                'value' => (int) ($counts[$key] ?? 0),
            ];
        }

        return $series;
    }

    /**
     * Open service requests grouped by a related dimension (PRD §24 charts).
     * Label defaults to "Unassigned" so a null foreign key is never lost.
     *
     * @return array<int, array<string, mixed>>
     */
    private function requestsByDimension(string $relation): array
    {
        $columns = ['business_division_id'];

        if ($relation === 'service') {
            $columns[] = 'service_id';
        }

        $rows = ServiceRequest::query()
            ->open()
            ->select(array_merge($columns, [DB::raw('count(*) as aggregate')]))
            ->with($relation.':id,name')
            ->groupBy($columns)
            ->orderByDesc('aggregate')
            ->limit(self::BREAKDOWN_LIMIT)
            ->get();

        return $rows
            ->map(function (ServiceRequest $request) use ($relation) {
                $related = $relation === 'service' ? $request->service : $request->division;

                return [
                    'label' => $related?->name ?? 'Unassigned',
                    'value' => (int) $request->aggregate,
                ];
            })
            ->values()
            ->all();
    }
}
