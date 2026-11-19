@extends('layouts.app')

@section('title', __('ui.nav.dashboard'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.dashboard_title') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.dashboard_subtitle') }}</p>
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
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.form-section :title="__('ui.shell_overview')" :description="__('ui.shell_overview_help')">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-lg border border-sme bg-canvas px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted">{{ __('ui.design_tokens') }}</p>
                        <p class="mt-1 text-sm">Indigo · Blue · Slate · Emerald/Amber/Rose</p>
                    </div>
                    <div class="rounded-lg border border-sme bg-canvas px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted">{{ __('ui.i18n') }}</p>
                        <p class="mt-1 text-sm">en / bn / ar · RTL when Arabic</p>
                    </div>
                </div>
            </x-ui.form-section>

            <x-ui.table :headers="[__('ui.col_module'), __('ui.col_status'), __('ui.col_phase')]">
                <tr>
                    <td class="px-4 py-3">{{ __('ui.design_system') }}</td>
                    <td class="px-4 py-3"><x-ui.badge tone="success">{{ __('ui.status_ready') }}</x-ui.badge></td>
                    <td class="px-4 py-3 text-muted">01</td>
                </tr>
                <tr>
                    <td class="px-4 py-3">{{ __('ui.layout_shell') }}</td>
                    <td class="px-4 py-3"><x-ui.badge tone="success">{{ __('ui.status_ready') }}</x-ui.badge></td>
                    <td class="px-4 py-3 text-muted">01</td>
                </tr>
                <tr>
                    <td class="px-4 py-3">{{ __('ui.tenancy_engine') }}</td>
                    <td class="px-4 py-3"><x-ui.badge tone="warning">{{ __('ui.status_later') }}</x-ui.badge></td>
                    <td class="px-4 py-3 text-muted">02+</td>
                </tr>
            </x-ui.table>
        </div>

        <aside class="space-y-6">
            <x-ui.form-section :title="__('ui.quick_checks')" :description="__('ui.quick_checks_help')">
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start gap-2">
                        <x-lucide-check-circle-2 class="mt-0.5 h-4 w-4 text-success-600" />
                        <span>{{ __('ui.check_theme') }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <x-lucide-check-circle-2 class="mt-0.5 h-4 w-4 text-success-600" />
                        <span>{{ __('ui.check_locale') }}</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <x-lucide-check-circle-2 class="mt-0.5 h-4 w-4 text-success-600" />
                        <span>{{ __('ui.check_sidebar') }}</span>
                    </li>
                </ul>
                <div class="mt-4">
                    <a href="{{ route('ui.kitchen-sink') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">
                        {{ __('ui.view_components') }} →
                    </a>
                </div>
            </x-ui.form-section>
        </aside>
    </div>
@endsection
