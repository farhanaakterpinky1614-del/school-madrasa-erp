@extends('layouts.app')

@section('title', __('ui.nav.permissions'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 03</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.permissions') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.permission_catalog_help') }}</p>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        @foreach ($catalog as $module => $items)
            <x-ui.form-section :title="ucfirst($module)">
                <ul class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $permission)
                        <li class="rounded-xl border border-sme px-3 py-2 font-mono text-xs">{{ $permission }}</li>
                    @endforeach
                </ul>
            </x-ui.form-section>
        @endforeach
    </div>
@endsection
