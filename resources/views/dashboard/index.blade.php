@extends('layouts.app')

@section('title', __('ui.nav.dashboard'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 01</p>
        <h1 class="text-2xl font-semibold tracking-tight sm:text-[1.7rem]">{{ __('ui.dashboard_title') }}</h1>
        <p class="mt-1.5 max-w-2xl text-sm text-muted">{{ __('ui.dashboard_subtitle') }}</p>
    </div>
    <div class="flex gap-2">
        <x-ui.button variant="outline" size="sm" type="button" disabled>
            <x-lucide-download class="h-4 w-4" />
            {{ __('ui.export') }}
        </x-ui.button>
        <x-ui.button size="sm" type="button" disabled>
            <x-lucide-plus class="h-4 w-4" />
            {{ __('ui.new_record') }}
        </x-ui.button>
    </div>
@endsection

@section('content')
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="sme-kpi sme-kpi-indigo px-4 py-4 ps-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.design_tokens') }}</p>
            <p class="mt-2 text-lg font-semibold tracking-tight">Indigo · Blue · Slate</p>
            <p class="mt-1 text-xs text-muted">Emerald / Amber / Rose status</p>
        </div>
        <div class="sme-kpi sme-kpi-emerald px-4 py-4 ps-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.i18n') }}</p>
            <p class="mt-2 text-lg font-semibold tracking-tight">en / bn / ar</p>
            <p class="mt-1 text-xs text-muted">RTL when Arabic</p>
        </div>
        <div class="sme-kpi sme-kpi-amber px-4 py-4 ps-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.layout_shell') }}</p>
            <p class="mt-2 text-lg font-semibold tracking-tight">{{ __('ui.status_ready') }}</p>
            <p class="mt-1 text-xs text-muted">Sidebar · Topbar · Canvas</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.form-section :title="__('ui.shell_overview')" :description="__('ui.shell_overview_help')">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-sme bg-gradient-to-br from-white/90 to-primary-50/50 px-4 py-3.5 shadow-sme-soft dark:from-slate-900/60 dark:to-primary-950/30">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.design_tokens') }}</p>
                        <p class="mt-1.5 text-sm leading-relaxed">Multi-stop Indigo → Blue gradients on slate atmosphere — soft depth, no neon.</p>
                    </div>
                    <div class="rounded-xl border border-sme bg-gradient-to-br from-white/90 to-secondary-50/50 px-4 py-3.5 shadow-sme-soft dark:from-slate-900/60 dark:to-secondary-950/25">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.i18n') }}</p>
                        <p class="mt-1.5 text-sm leading-relaxed">English · বাংলা · العربية with logical CSS and RTL shell flip.</p>
                    </div>
                </div>
            </x-ui.form-section>

            <x-ui.table :headers="[__('ui.col_module'), __('ui.col_status'), __('ui.col_phase')]">
                <tr class="transition hover:bg-primary-50/40 dark:hover:bg-white/[0.03]">
                    <td class="px-4 py-3.5 font-medium">{{ __('ui.design_system') }}</td>
                    <td class="px-4 py-3.5"><x-ui.badge tone="success">{{ __('ui.status_ready') }}</x-ui.badge></td>
                    <td class="px-4 py-3.5 text-muted">01</td>
                </tr>
                <tr class="transition hover:bg-primary-50/40 dark:hover:bg-white/[0.03]">
                    <td class="px-4 py-3.5 font-medium">{{ __('ui.layout_shell') }}</td>
                    <td class="px-4 py-3.5"><x-ui.badge tone="success">{{ __('ui.status_ready') }}</x-ui.badge></td>
                    <td class="px-4 py-3.5 text-muted">01</td>
                </tr>
                <tr class="transition hover:bg-primary-50/40 dark:hover:bg-white/[0.03]">
                    <td class="px-4 py-3.5 font-medium">{{ __('ui.tenancy_engine') }}</td>
                    <td class="px-4 py-3.5"><x-ui.badge tone="warning">{{ __('ui.status_later') }}</x-ui.badge></td>
                    <td class="px-4 py-3.5 text-muted">02+</td>
                </tr>
            </x-ui.table>
        </div>

        <aside class="space-y-6">
            <x-ui.form-section :title="__('ui.quick_checks')" :description="__('ui.quick_checks_help')">
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start gap-2.5">
                        <span class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-gradient-to-br from-success-600 to-emerald-400 text-white">
                            <x-lucide-check class="h-3 w-3" />
                        </span>
                        <span>{{ __('ui.check_theme') }}</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-gradient-to-br from-success-600 to-emerald-400 text-white">
                            <x-lucide-check class="h-3 w-3" />
                        </span>
                        <span>{{ __('ui.check_locale') }}</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-gradient-to-br from-success-600 to-emerald-400 text-white">
                            <x-lucide-check class="h-3 w-3" />
                        </span>
                        <span>{{ __('ui.check_sidebar') }}</span>
                    </li>
                </ul>
                <div class="mt-5">
                    <a href="{{ route('ui.kitchen-sink') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">
                        {{ __('ui.view_components') }}
                        <x-lucide-arrow-right class="h-4 w-4 rtl:rotate-180" />
                    </a>
                </div>
            </x-ui.form-section>
        </aside>
    </div>
@endsection
