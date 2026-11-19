@extends('layouts.app')

@section('title', $institution->name)

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $institution->name }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.linked_tenant') }}: {{ $institution->tenant?->name }}</p>
    </div>
@endsection

@section('content')
    <x-ui.form-section :title="__('ui.tenant_details')">
        <dl class="grid gap-3 sm:grid-cols-2 text-sm">
            <div><dt class="text-muted">{{ __('ui.institution_type') }}</dt><dd class="font-medium">{{ config('sme.institution_types.'.$institution->institution_type) }}</dd></div>
            <div><dt class="text-muted">{{ __('ui.field_email') }}</dt><dd class="font-medium">{{ $institution->email ?? '—' }}</dd></div>
            <div><dt class="text-muted">{{ __('ui.phone') }}</dt><dd class="font-medium">{{ $institution->phone ?? '—' }}</dd></div>
            <div><dt class="text-muted">{{ __('ui.plan') }}</dt><dd class="font-medium">{{ $institution->tenant?->plan?->name }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-muted">{{ __('ui.address') }}</dt><dd class="font-medium">{{ $institution->address ?? '—' }}</dd></div>
        </dl>
        <div class="mt-4">
            <a href="{{ route('platform.tenants.show', $institution->tenant) }}" class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ __('ui.view_tenant') }}</a>
        </div>
    </x-ui.form-section>
@endsection
