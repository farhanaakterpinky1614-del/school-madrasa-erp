@extends('layouts.app')

@section('title', __('ui.nav.modules'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.module_access') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.tenant_modules_help') }}</p>
    </div>
@endsection

@section('content')
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($modules as $module)
            <div class="rounded-2xl border border-sme sme-surface-solid p-4 shadow-sme-soft">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold">{{ $module['name'] }}</p>
                        <p class="mt-1 text-xs text-muted">{{ $module['description'] }}</p>
                    </div>
                    <x-ui.badge :tone="$module['accessible'] ? 'success' : 'warning'">
                        {{ $module['accessible'] ? __('ui.enabled') : __('ui.locked') }}
                    </x-ui.badge>
                </div>
            </div>
        @endforeach
    </div>
@endsection
