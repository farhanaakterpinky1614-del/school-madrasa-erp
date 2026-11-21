@extends('layouts.app')

@section('title', __('ui.new_payroll_run'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.new_payroll_run') }}</h1>
    </div>
@endsection

@section('content')
<form method="POST" action="{{ route('tenant.payroll.store') }}" class="max-w-xl space-y-4">@csrf
    <x-ui.input name="period_year" type="number" min="2020" max="2100" :label="__('ui.period_year')" :value="old('period_year', now('Asia/Dhaka')->year)" required />
    <x-ui.input name="period_month" type="number" min="1" max="12" :label="__('ui.period_month')" :value="old('period_month', now('Asia/Dhaka')->month)" required />
    <x-ui.textarea name="notes" :label="__('ui.notes')" :value="old('notes')" />
    <div class="flex gap-3"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        <a href="{{ route('tenant.payroll.index') }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a></div>
</form>
@endsection
