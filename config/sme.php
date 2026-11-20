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
    | Security (Phase 03) — detailed rules live in config/identity.php
    |--------------------------------------------------------------------------
    */
    'security' => [
        'session_lifetime_minutes' => (int) env('SESSION_LIFETIME', 120),
        'password_min' => (int) env('SME_PASSWORD_MIN', 8),
    ],

];
