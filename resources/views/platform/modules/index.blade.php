@extends('layouts.app')

@section('title', __('ui.nav.modules'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.module_registry') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.modules_help') }}</p>
    </div>
@endsection

@section('content')
    <x-ui.table :headers="[__('ui.col_name'), 'Key', 'Feature', __('ui.col_status')]">
        @foreach ($modules as $module)
            <tr class="transition hover:bg-primary-50/40 dark:hover:bg-white/[0.03]">
                <td class="px-4 py-3.5 font-medium">{{ $module->name }}</td>
                <td class="px-4 py-3.5 text-muted">{{ $module->key }}</td>
                <td class="px-4 py-3.5 text-muted">{{ $module->feature_key ?? ($module->is_core ? 'core' : '—') }}</td>
                <td class="px-4 py-3.5">
                    <x-ui.badge :tone="$module->is_active ? 'success' : 'warning'">
                        {{ $module->is_core ? 'core' : ($module->is_active ? 'gated' : 'off') }}
                    </x-ui.badge>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
@endsection
