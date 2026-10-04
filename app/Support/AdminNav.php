<?php

namespace App\Support;

use App\Models\Enquiry;
use App\Models\QuoteRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Admin sidebar navigation — Design System §26, prototype-docs/admin.html.
 *
 * Declared once so every admin screen shares one menu. Items are filtered by
 * permission, so nobody is shown a link they cannot open, and modules that have
 * not shipped yet are flagged pending and rendered disabled rather than linking
 * to a route that does not exist.
 */
final class AdminNav
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function items(?User $user): array
    {
        $items = [
            [
                'label' => 'Dashboard', 'icon' => 'layout-grid',
                'route' => 'admin.dashboard', 'active' => 'admin.dashboard',
                'permission' => 'view-admin-dashboard',
            ],
            [
                'label' => 'Business Divisions', 'icon' => 'building',
                'permission' => 'view-divisions', 'pending' => 'Module 6',
            ],
            [
                'label' => 'Services', 'icon' => 'briefcase',
                'permission' => 'view-services', 'pending' => 'Module 6',
            ],
            [
                'label' => 'Enquiries & Requests', 'icon' => 'file-text',
                'permission' => 'view-enquiries', 'pending' => 'Module 4',
                'count' => true,
            ],
            [
                'label' => 'Audit Logs', 'icon' => 'shield-check',
                'route' => 'admin.audit-logs.index', 'active' => 'admin.audit-logs.*',
                'permission' => 'view-audit-logs',
            ],
            [
                'label' => 'Users & Roles', 'icon' => 'users',
                'permission' => 'manage-users', 'pending' => 'Module 14',
            ],
        ];

        $visible = [];

        foreach ($items as $item) {
            if ($user === null || ! $user->can($item['permission'])) {
                continue;
            }

            $href = isset($item['route']) ? route($item['route']) : null;
            $isActive = isset($item['active']) && Route::is($item['active']);
            $count = ! empty($item['count']) ? self::openTriageCount() : null;

            unset($item['route'], $item['active'], $item['permission'], $item['count']);

            $item['href'] = $href;
            $item['is_active'] = $isActive;
            $item['count'] = $count;

            $visible[] = $item;
        }

        return $visible;
    }

    /**
     * Unhandled enquiries, service requests and quotes awaiting action. Shown as
     * the sidebar badge so staff can see the queue depth from any admin screen.
     */
    public static function openTriageCount(): int
    {
        return Enquiry::query()->open()->count()
            + ServiceRequest::query()->open()->count()
            + QuoteRequest::query()->actionable()->count();
    }
}
