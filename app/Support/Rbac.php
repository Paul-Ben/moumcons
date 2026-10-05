<?php

namespace App\Support;

/**
 * Single source of truth for MOAUM roles and permissions.
 *
 * Mirrors prototype-docs/MOAUM_PRD_SRD.md §23 (Authentication and RBAC).
 * Authorization must check *permissions* (via policies / can: middleware),
 * never role names directly, per the PRD guidance.
 */
final class Rbac
{
    /** Roles (PRD §23). */
    public const SUPER_ADMINISTRATOR = 'Super Administrator';

    public const ADMINISTRATOR = 'Administrator';

    public const CONTENT_EDITOR = 'Content Editor';

    public const BUSINESS_MANAGER = 'Business Manager';

    public const CUSTOMER = 'Customer';

    public const TRAINING_PARTICIPANT = 'Training Participant';

    /** All roles in assignment-priority order. */
    public static function roles(): array
    {
        return [
            self::SUPER_ADMINISTRATOR,
            self::ADMINISTRATOR,
            self::CONTENT_EDITOR,
            self::BUSINESS_MANAGER,
            self::CUSTOMER,
            self::TRAINING_PARTICIPANT,
        ];
    }

    /**
     * Permissions grouped by area (PRD §23 recommended examples, extended
     * for every content module so CRUD screens can gate consistently).
     *
     * @return array<string, array<int, string>>
     */
    public static function permissionGroups(): array
    {
        return [
            'pages' => ['view-pages', 'create-pages', 'edit-pages', 'publish-pages', 'delete-pages'],
            'services' => ['view-services', 'create-services', 'edit-services', 'publish-services', 'delete-services'],
            'divisions' => ['view-divisions', 'create-divisions', 'edit-divisions', 'publish-divisions', 'delete-divisions'],
            'projects' => ['view-projects', 'create-projects', 'edit-projects', 'publish-projects', 'delete-projects'],
            'news' => ['view-news', 'create-news', 'edit-news', 'publish-news', 'delete-news'],
            'training' => ['view-training', 'create-training', 'edit-training', 'publish-training', 'delete-training'],
            'careers' => ['view-careers', 'create-careers', 'edit-careers', 'publish-careers', 'delete-careers'],
            // Applications hold applicants' personal data and CVs (PRD §33), so
            // reading them is separate from managing the job adverts.
            'applications' => ['view-applications', 'update-applications'],
            'downloads' => ['view-downloads', 'create-downloads', 'edit-downloads', 'publish-downloads', 'delete-downloads'],
            'faqs' => ['view-faqs', 'create-faqs', 'edit-faqs', 'publish-faqs', 'delete-faqs'],
            'gallery' => ['view-gallery', 'create-gallery', 'edit-gallery', 'publish-gallery', 'delete-gallery'],
            'media' => ['view-media', 'upload-media', 'delete-media'],
            'enquiries' => ['view-enquiries', 'assign-enquiries', 'update-enquiries', 'close-enquiries'],
            'requests' => ['view-service-requests', 'update-service-requests', 'view-quotes', 'create-quotes', 'update-quotes', 'close-quotes'],
            'users' => ['manage-users', 'manage-roles'],
            'settings' => ['manage-settings'],
            'audit' => ['view-audit-logs'],
            'dashboard' => ['view-admin-dashboard'],
        ];
    }

    /**
     * @return array<int, string> flat list of every permission name
     */
    public static function allPermissions(): array
    {
        return array_merge(...array_values(self::permissionGroups()));
    }

    /**
     * Permission map per role. Super Administrators bypass via Gate::before,
     * but we still grant explicit sets to the other roles.
     *
     * @return array<string, array<int, string>>
     */
    public static function rolePermissions(): array
    {
        $contentView = ['view-pages', 'view-services', 'view-divisions', 'view-projects', 'view-news', 'view-training', 'view-careers', 'view-media',
            'view-downloads', 'view-faqs', 'view-gallery'];
        $contentWrite = ['create-pages', 'edit-pages', 'publish-pages',
            'create-services', 'edit-services', 'publish-services',
            'create-divisions', 'edit-divisions', 'publish-divisions',
            'create-projects', 'edit-projects', 'publish-projects',
            'create-news', 'edit-news', 'publish-news',
            'create-training', 'edit-training', 'publish-training',
            'create-careers', 'edit-careers', 'publish-careers',
            'create-downloads', 'edit-downloads', 'publish-downloads',
            'create-faqs', 'edit-faqs', 'publish-faqs',
            'create-gallery', 'edit-gallery', 'publish-gallery',
            'upload-media'];

        return [
            // Full access — granted implicitly through Gate::before() as well.
            self::SUPER_ADMINISTRATOR => self::allPermissions(),

            self::ADMINISTRATOR => array_unique(array_merge(
                self::allPermissions(),
            )),

            self::CONTENT_EDITOR => array_unique(array_merge(
                $contentView,
                $contentWrite,
                ['view-admin-dashboard'],
            )),

            self::BUSINESS_MANAGER => [
                'view-admin-dashboard',
                'view-divisions', 'edit-divisions', 'publish-divisions',
                'view-services', 'create-services', 'edit-services', 'publish-services',
                'view-enquiries', 'assign-enquiries', 'update-enquiries', 'close-enquiries',
                'view-service-requests', 'update-service-requests',
                'view-quotes', 'create-quotes', 'update-quotes', 'close-quotes',
                'view-media', 'upload-media',
            ],

            self::CUSTOMER => [
                // Customers interact through public forms; no dashboard access yet.
            ],

            self::TRAINING_PARTICIPANT => [
                // Reserved for the future training portal (out of MVP scope).
            ],
        ];
    }
}
