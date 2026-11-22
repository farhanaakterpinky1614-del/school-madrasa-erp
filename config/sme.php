<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported locales (Phase 01 foundation)
    |--------------------------------------------------------------------------
    */

    'locales' => [
        'en' => [
            'label' => 'English',
            'dir' => 'ltr',
            'native' => 'English',
        ],
        'bn' => [
            'label' => 'Bengali',
            'dir' => 'ltr',
            'native' => 'বাংলা',
        ],
        'ar' => [
            'label' => 'Arabic',
            'dir' => 'rtl',
            'native' => 'العربية',
        ],
    ],

    'default_locale' => 'en',

    'currency' => 'BDT',

    'timezone' => 'Asia/Dhaka',

    /*
    |--------------------------------------------------------------------------
    | Institution types
    |--------------------------------------------------------------------------
    */

    'institution_types' => [
        'school' => 'General School',
        'college' => 'College',
        'alia' => 'Alia Madrasa',
        'qawmi' => 'Qawmi Madrasa',
        'hifz' => 'Hifz Madrasa',
    ],

    /*
    |--------------------------------------------------------------------------
    | Institution type presets (Phase 04) — config-driven, no codebase fork
    |--------------------------------------------------------------------------
    */
    'institution_presets' => [
        'school' => [
            'label' => 'General School',
            'features' => [
                'classes_sections' => true,
                'departments' => false,
                'standard_subjects' => true,
                'hifz' => false,
                'board_exam_fields' => true,
            ],
            'terminology' => [
                'class' => 'Class',
                'section' => 'Section',
                'department' => 'Department',
                'teacher' => 'Teacher',
                'principal' => 'Principal',
            ],
            'default_classes' => [6, 7, 8, 9, 10],
        ],
        'college' => [
            'label' => 'College',
            'features' => [
                'classes_sections' => true,
                'departments' => true,
                'standard_subjects' => true,
                'hifz' => false,
                'board_exam_fields' => true,
            ],
            'terminology' => [
                'class' => 'Year',
                'section' => 'Group',
                'department' => 'Department',
                'teacher' => 'Teacher',
                'principal' => 'Principal',
            ],
            'default_classes' => ['XI', 'XII'],
        ],
        'alia' => [
            'label' => 'Alia Madrasa',
            'features' => [
                'classes_sections' => true,
                'departments' => false,
                'standard_subjects' => true,
                'hifz' => false,
                'board_exam_fields' => true,
            ],
            'terminology' => [
                'class' => 'Class',
                'section' => 'Section',
                'department' => 'Department',
                'teacher' => 'Teacher',
                'principal' => 'Principal',
            ],
            'default_classes' => [6, 7, 8, 9, 10],
        ],
        'qawmi' => [
            'label' => 'Qawmi Madrasa',
            'features' => [
                'classes_sections' => false,
                'departments' => true,
                'standard_subjects' => true,
                'hifz' => false,
                'board_exam_fields' => false,
            ],
            'terminology' => [
                'class' => 'Darajah',
                'section' => 'Halaqah',
                'department' => 'Kitab',
                'teacher' => 'Ustaz',
                'principal' => 'Muhtamim',
            ],
            'default_classes' => ['Ibtedayee', 'Mutawassit', 'Sanabi'],
        ],
        'hifz' => [
            'label' => 'Hifz Madrasa',
            'features' => [
                'classes_sections' => false,
                'departments' => false,
                'standard_subjects' => false,
                'hifz' => true,
                'board_exam_fields' => false,
            ],
            'terminology' => [
                'class' => 'Circle',
                'section' => 'Group',
                'department' => 'Department',
                'teacher' => 'Ustaz',
                'principal' => 'Muhtamim',
            ],
            'default_classes' => ['Nazira', 'Hifz', 'Dawr'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenancy
    |--------------------------------------------------------------------------
    */

    'tenancy' => [
        'base_domain' => env('TENANCY_BASE_DOMAIN', 'localhost'),

        'central_domains' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TENANCY_CENTRAL_DOMAINS', 'localhost,127.0.0.1,0.0.0.0'))
        ))),

        /*
         * When true (default in local), hosts that are not a known tenant subdomain/custom
         * domain are treated as central — keeps Try Live / preview hosts usable.
         */
        'treat_unknown_hosts_as_central' => (bool) env(
            'TENANCY_UNKNOWN_AS_CENTRAL',
            env('APP_ENV', 'production') === 'local'
        ),

        'platform_hosts' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TENANCY_PLATFORM_HOSTS', 'admin.localhost'))
        ))),

        'reserved_slugs' => [
            'www', 'admin', 'api', 'mail', 'smtp', 'ftp', 'app', 'platform',
            'static', 'assets', 'cdn', 'status', 'support', 'help', 'billing',
        ],

        /*
         * When true, on central hosts bind CurrentTenant from the authenticated
         * user's tenant_id (local/demo convenience). Production should rely on
         * host resolution and keep this false.
         */
        'allow_user_tenant_fallback' => (bool) env('TENANCY_USER_FALLBACK', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security (Phase 03 + Phase 16 hardening)
    |--------------------------------------------------------------------------
    */
    'security' => [
        'session_lifetime_minutes' => (int) env('SESSION_LIFETIME', 120),
        'password_min' => (int) env('SME_PASSWORD_MIN', 8),
        'headers_enabled' => (bool) env('SME_SECURITY_HEADERS', true),
        /*
         * Practical CSP for Blade + Vite + Alpine/Livewire — report-only by default
         * so local Vite HMR / inline Alpine bootstraps are not blocked hard.
         */
        'csp_report_only' => (bool) env('SME_CSP_REPORT_ONLY', true),
        'csp' => env(
            'SME_CSP',
            "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline' https://fonts.bunny.net; font-src 'self' https://fonts.bunny.net data:; img-src 'self' data: blob:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'"
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks (Phase 16)
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'delivery_mode' => env('SME_WEBHOOK_MODE', 'stub'), // stub|http
        'timeout_seconds' => (int) env('SME_WEBHOOK_TIMEOUT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom domain verification (Phase 16)
    |--------------------------------------------------------------------------
    */
    'domain_verification' => [
        // dns = real DNS/HTTP checks; always_pass = tests/local convenience
        'driver' => env('SME_DOMAIN_VERIFY_DRIVER', 'dns'),
        'allow_live_http' => (bool) env('SME_DOMAIN_VERIFY_HTTP', false),
        'txt_overrides' => [],
        'http_overrides' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Premium UI pack hook points (Phase 16) — flags only, no full redesign
    |--------------------------------------------------------------------------
    */
    'ui_packs' => [
        'default' => 'indigo_slate',
        'packs' => [
            'indigo_slate' => [
                'label' => 'Indigo / Slate',
                'description' => 'Default SME brand — indigo primary on slate surfaces.',
                'premium' => false,
            ],
            'emerald_slate' => [
                'label' => 'Emerald Accent',
                'description' => 'Premium accent pack hook (emerald highlights).',
                'premium' => true,
            ],
            'navy_gold' => [
                'label' => 'Navy & Gold',
                'description' => 'Premium ceremonial pack hook for enterprise branding.',
                'premium' => true,
            ],
        ],
    ],

];
