<?php

/**
 * Identity / RBAC catalogue for School & Madrasa ERP (Phase 03).
 *
 * Permission naming: {module}.{resource}.{action}
 * Spatie teams mode: team FK = tenant_id (see config/permission.php).
 */

return [

    /*
    | Platform Spatie team id for model_has_* pivots (roles themselves keep tenant_id null).
    */
    'platform_team_id' => 0,

    'password' => [
        'min_length' => (int) env('SME_PASSWORD_MIN', 8),
        'require_mixed_case' => (bool) env('SME_PASSWORD_MIXED', false),
        'require_number' => (bool) env('SME_PASSWORD_NUMBER', true),
        'require_symbol' => (bool) env('SME_PASSWORD_SYMBOL', false),
    ],

    'rate_limits' => [
        'login' => (int) env('SME_RATE_LOGIN', 5), // per minute per IP+email
        'password_reset' => (int) env('SME_RATE_RESET', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Global permission catalogue (guard: web)
    | Platform permissions are assigned with tenant_id = null (global roles).
    | Tenant permissions are assigned via tenant-scoped roles.
    |--------------------------------------------------------------------------
    */
    'permissions' => [

        // Platform
        'platform.tenants.view',
        'platform.tenants.create',
        'platform.tenants.update',
        'platform.tenants.suspend',
        'platform.plans.view',
        'platform.modules.view',
        'platform.usage.view',
        'platform.users.view',
        'platform.users.create',
        'platform.users.update',
        'platform.audit.view',
        'platform.impersonate',

        // Tenant settings / identity
        'settings.institution.view',
        'settings.institution.update',
        'settings.users.view',
        'settings.users.create',
        'settings.users.update',
        'settings.users.delete',
        'settings.roles.view',
        'settings.roles.create',
        'settings.roles.update',
        'settings.roles.delete',
        'settings.permissions.view',
        'settings.memberships.view',
        'settings.memberships.create',
        'settings.memberships.update',
        'settings.memberships.delete',
        'settings.audit.view',
        'settings.login_history.view',
        'settings.sessions.view',
        'settings.sessions.revoke',
        'settings.domains.view',
        'settings.domains.update',
        'settings.subscription.view',
        'settings.modules.view',
        'settings.usage.view',
        'settings.usage.update',

        // Branch / academics setup (Phase 04)
        'branches.branch.view',
        'branches.branch.manage',
        'academics.department.view',
        'academics.department.manage',
        'academics.year.view',
        'academics.year.manage',
        'academics.term.view',
        'academics.term.manage',
        'academics.shift.view',
        'academics.shift.manage',
        'academics.class.view',
        'academics.class.manage',
        'academics.section.view',
        'academics.section.manage',
        'academics.subject.view',
        'academics.subject.manage',
        'academics.curriculum.view',
        'academics.curriculum.manage',
        'academics.grading.view',
        'academics.grading.manage',
        'academics.calendar.view',
        'academics.calendar.manage',
        'settings.branding.view',
        'settings.branding.update',

        // Placeholder module permissions used by role templates (enforced when modules ship)
        'students.student.view',
        'students.student.create',
        'students.student.update',
        'fees.payment.view',
        'fees.payment.create',
        'reports.export.create',
    ],

    /*
    |--------------------------------------------------------------------------
    | Platform roles (tenant_id = null)
    |--------------------------------------------------------------------------
    */
    'platform_roles' => [
        'Super Admin' => [
            'platform.tenants.view',
            'platform.tenants.create',
            'platform.tenants.update',
            'platform.tenants.suspend',
            'platform.plans.view',
            'platform.modules.view',
            'platform.usage.view',
            'platform.users.view',
            'platform.users.create',
            'platform.users.update',
            'platform.audit.view',
            'platform.impersonate',
        ],
        'Support Agent' => [
            'platform.tenants.view',
            'platform.plans.view',
            'platform.modules.view',
            'platform.usage.view',
            'platform.users.view',
            'platform.audit.view',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant role templates (cloned per tenant on provision)
    |--------------------------------------------------------------------------
    */
    'tenant_role_templates' => [
        'Institution Admin' => [
            // All tenant-scoped permissions (filled at runtime from catalogue minus platform.*)
            '*tenant*',
        ],
        'Principal' => [
            'settings.institution.view',
            'settings.branding.view',
            'settings.users.view',
            'settings.roles.view',
            'settings.permissions.view',
            'settings.memberships.view',
            'settings.audit.view',
            'settings.login_history.view',
            'settings.domains.view',
            'settings.subscription.view',
            'settings.modules.view',
            'settings.usage.view',
            'branches.branch.view',
            'branches.branch.manage',
            'academics.department.view',
            'academics.year.view',
            'academics.year.manage',
            'academics.term.view',
            'academics.term.manage',
            'academics.shift.view',
            'academics.class.view',
            'academics.class.manage',
            'academics.section.view',
            'academics.section.manage',
            'academics.subject.view',
            'academics.subject.manage',
            'academics.curriculum.view',
            'academics.curriculum.manage',
            'academics.grading.view',
            'academics.grading.manage',
            'academics.calendar.view',
            'academics.calendar.manage',
            'students.student.view',
            'fees.payment.view',
            'reports.export.create',
        ],
        'Academic Officer' => [
            'settings.institution.view',
            'branches.branch.view',
            'academics.department.view',
            'academics.department.manage',
            'academics.year.view',
            'academics.year.manage',
            'academics.term.view',
            'academics.term.manage',
            'academics.shift.view',
            'academics.shift.manage',
            'academics.class.view',
            'academics.class.manage',
            'academics.section.view',
            'academics.section.manage',
            'academics.subject.view',
            'academics.subject.manage',
            'academics.curriculum.view',
            'academics.curriculum.manage',
            'academics.grading.view',
            'academics.grading.manage',
            'academics.calendar.view',
            'academics.calendar.manage',
            'students.student.view',
            'students.student.create',
            'students.student.update',
        ],
        'Teacher' => [
            'academics.class.view',
            'academics.section.view',
            'academics.subject.view',
            'academics.calendar.view',
            'students.student.view',
        ],
        'Accountant' => [
            'students.student.view',
            'fees.payment.view',
            'fees.payment.create',
            'reports.export.create',
        ],
        'HR Officer' => [
            'settings.users.view',
            'settings.users.create',
            'settings.users.update',
            'settings.memberships.view',
            'settings.memberships.create',
            'settings.memberships.update',
        ],
        'Librarian' => [
            'students.student.view',
        ],
        'Receptionist' => [
            'students.student.view',
            'students.student.create',
        ],
        'Parent' => [
            'students.student.view',
        ],
        'Student' => [
            'students.student.view',
        ],
    ],

];
