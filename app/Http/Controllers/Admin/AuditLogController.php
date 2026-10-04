<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Admin → Audit Logs (PRD §31 /admin/audit-logs, §32 Audit logging).
 *
 * Read-only viewer: the trail is append-only by design, so this module
 * intentionally exposes no create/edit/delete endpoints. Access requires
 * the 'view-audit-logs' permission (Rbac::permissionGroups()['audit']).
 */
class AuditLogController extends Controller
{
    // Access control lives in routes/web.php: the admin.* group applies 'auth'
    // and each route applies 'permission:view-audit-logs'. Do not add a
    // constructor calling $this->middleware() — the base Controller does not
    // implement HasMiddleware, so that call is a fatal error.

    /** GET /admin/audit-logs — filterable, paginated trail. */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'action' => ['nullable', 'string', 'max:100'],
            'user' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $logs = AuditLog::query()
            ->with('user')
            ->forUser($filters['user'] ?? null)
            ->action($filters['action'] ?? null)
            ->between($filters['from'] ?? null, $filters['to'] ?? null)
            ->when($filters['q'] ?? null, function (Builder $query, string $term): void {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

                $query->where(function (Builder $q) use ($like): void {
                    $q->where('description', 'like', $like)
                        ->orWhere('action', 'like', $like)
                        ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', $like));
                });
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        // Distinct action prefixes for the filter dropdown (e.g. "enquiry").
        // Portable across MySQL/SQLite: fetch + reduce in PHP (log volume is
        // bounded by pagination anyway; group cache keeps it cheap).
        $actionGroups = AuditLog::query()
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->map(fn (string $action) => Str::before($action, '.'))
            ->unique()
            ->sort()
            ->values();

        $actors = User::query()
            ->whereHas('roles')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->keyBy('id');

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'actionGroups' => $actionGroups,
            'actors' => $actors,
        ]);
    }

    /** GET /admin/audit-logs/{auditLog} — entry detail incl. property diff. */
    public function show(AuditLog $auditLog): View
    {
        $auditLog->load('user', 'subject');

        return view('admin.audit-logs.show', ['log' => $auditLog]);
    }
}
