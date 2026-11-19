@extends('layouts.app')

@section('title', __('ui.nav.institutions'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.institutions') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.institutions_help') }}</p>
    </div>
@endsection

@section('content')
    <x-ui.table :headers="[__('ui.col_name'), __('ui.nav.tenants'), __('ui.institution_type'), __('ui.plan'), __('ui.col_action')]">
        @foreach ($institutions as $institution)
            <tr class="transition hover:bg-primary-50/40 dark:hover:bg-white/[0.03]">
                <td class="px-4 py-3.5 font-medium">{{ $institution->name }}</td>
                <td class="px-4 py-3.5 text-muted">{{ $institution->tenant?->slug }}</td>
                <td class="px-4 py-3.5">{{ config('sme.institution_types.'.$institution->institution_type) }}</td>
                <td class="px-4 py-3.5">{{ $institution->tenant?->plan?->name }}</td>
                <td class="px-4 py-3.5">
                    <a href="{{ route('platform.institutions.show', $institution->id) }}" class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ __('ui.view') }}</a>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
    <div class="mt-4">{{ $institutions->links() }}</div>
@endsection
