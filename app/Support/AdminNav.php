<?php

namespace App\Support;

use App\Models\Enquiry;
use App\Models\JobApplication;
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
        $sections = [
            null => [
                ['label' => 'Dashboard', 'icon' => 'layout-grid', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'permission' => 'view-admin-dashboard'],
            ],
            'Customer engagement' => [
                ['label' => 'Enquiries', 'icon' => 'mail', 'route' => 'admin.enquiries.index', 'active' => 'admin.enquiries.*', 'permission' => 'view-enquiries', 'count' => 'enquiries'],
                ['label' => 'Service Requests', 'icon' => 'file-text', 'route' => 'admin.service-requests.index', 'active' => 'admin.service-requests.*', 'permission' => 'view-service-requests', 'count' => 'service-requests'],
                ['label' => 'Quote Requests', 'icon' => 'sliders-horizontal', 'route' => 'admin.quote-requests.index', 'active' => 'admin.quote-requests.*', 'permission' => 'view-quotes', 'count' => 'quotes'],
                ['label' => 'Applications', 'icon' => 'users', 'route' => 'admin.applications.index', 'active' => 'admin.applications.*', 'permission' => 'view-applications', 'count' => 'applications'],
            ],
            'Content' => [
                ['label' => 'Business Divisions', 'icon' => 'building', 'route' => 'admin.divisions.index', 'active' => 'admin.divisions.*', 'permission' => 'view-divisions'],
                ['label' => 'Services', 'icon' => 'briefcase', 'route' => 'admin.services.index', 'active' => ['admin.services.*', 'admin.service-categories.*'], 'permission' => 'view-services'],
                ['label' => 'Projects', 'icon' => 'hard-hat', 'route' => 'admin.projects.index', 'active' => 'admin.projects.*', 'permission' => 'view-projects'],
                ['label' => 'Training', 'icon' => 'graduation-cap', 'route' => 'admin.training.index', 'active' => 'admin.training.*', 'permission' => 'view-training'],
                ['label' => 'News', 'icon' => 'newspaper', 'route' => 'admin.news.index', 'active' => ['admin.news.*', 'admin.news-categories.*'], 'permission' => 'view-news'],
                ['label' => 'Pages', 'icon' => 'folder', 'route' => 'admin.pages.index', 'active' => ['admin.pages.*', 'admin.leadership.*'], 'permission' => 'view-pages'],
                ['label' => 'Careers', 'icon' => 'briefcase', 'route' => 'admin.jobs.index', 'active' => 'admin.jobs.*', 'permission' => 'view-careers'],
                ['label' => 'Downloads', 'icon' => 'download', 'route' => 'admin.documents.index', 'active' => 'admin.documents.*', 'permission' => 'view-downloads'],
                ['label' => 'Gallery', 'icon' => 'image', 'route' => 'admin.galleries.index', 'active' => 'admin.galleries.*', 'permission' => 'view-gallery'],
                ['label' => 'FAQs', 'icon' => 'help-circle', 'route' => 'admin.faqs.index', 'active' => 'admin.faqs.*', 'permission' => 'view-faqs'],
                ['label' => 'Media Library', 'icon' => 'image', 'route' => 'admin.media.index', 'active' => 'admin.media.*', 'permission' => 'view-media'],
            ],
            'System' => [
                ['label' => 'Users & Roles', 'icon' => 'key', 'route' => 'admin.users.index', 'active' => ['admin.users.*', 'admin.roles.*'], 'permission' => 'manage-users'],
                ['label' => 'Settings', 'icon' => 'settings', 'route' => 'admin.settings.edit', 'active' => 'admin.settings.*', 'permission' => 'manage-settings'],
                ['label' => 'Audit Logs', 'icon' => 'shield-check', 'route' => 'admin.audit-logs.index', 'active' => 'admin.audit-logs.*', 'permission' => 'view-audit-logs'],
            ],
        ];

        // Flattened with a section heading on each item; the layout prints a
        // heading whenever it changes, so empty sections simply disappear.
        $items = [];
        foreach ($sections as $section => $entries) {
            foreach ($entries as $entry) {
                $items[] = $entry + ['section' => $section ?: null];
            }
        }

        $visible = [];

        foreach ($items as $item) {
            if ($user === null || ! $user->can($item['permission'])) {
                continue;
            }

            $href = isset($item['route']) ? route($item['route']) : null;
            $isActive = isset($item['active']) && Route::is(...(array) $item['active']);
            $count = match ($item['count'] ?? null) {
                'enquiries' => self::openEnquiryCount(),
                'service-requests' => ServiceRequest::query()->open()->count(),
                'quotes' => QuoteRequest::query()->actionable()->count(),
                'applications' => JobApplication::query()->where('status', 'received')->count(),
                'all' => self::openTriageCount(),
                default => null,
            };

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

    /**
     * Open enquiries only. The badge sits next to the enquiries link, so it
     * counts what that screen actually lists; the dashboard's "open items"
     * widget uses openTriageCount() for the whole cross-flow picture.
     */
    public static function openEnquiryCount(): int
    {
        return Enquiry::query()->open()->count();
    }
}
