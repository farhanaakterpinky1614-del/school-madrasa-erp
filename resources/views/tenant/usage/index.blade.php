@extends('layouts.app')

@section('title', __('ui.nav.usage'))

@section('page-header')
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.usage') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ __('ui.usage_stub_help') }}</p>
    </div>
@endsection

@section('content')
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        @foreach ($usage as $key => $row)
            <div class="sme-kpi sme-kpi-indigo px-4 py-4 ps-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ __('ui.usage_'.$key) }}</p>
                <p class="mt-2 text-2xl font-semibold">{{ number_format($row['current']) }}</p>
                <p class="mt-1 text-xs text-muted">{{ __('ui.limit') }}: {{ $row['limit'] === null ? '∞' : number_format($row['limit']) }}</p>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('tenant.usage.stub') }}" class="max-w-xl">
        @csrf
        @method('PUT')
        <x-ui.form-section :title="__('ui.update_usage_stub')" :description="__('ui.usage_stub_help')">
            <div class="space-y-4">
                <x-ui.input name="students" type="number" min="0" :label="__('ui.usage_students')" :value="old('students', $usage['students']['current'])" required />
                <x-ui.input name="staff" type="number" min="0" :label="__('ui.usage_staff')" :value="old('staff', $usage['staff']['current'])" required />
                <x-ui.input name="storage_mb" type="number" min="0" :label="__('ui.usage_storage')" :value="old('storage_mb', $usage['storage_mb']['current'])" required />
            </div>
            <div class="mt-6">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
            </div>
        </x-ui.form-section>
    </form>
@endsection
