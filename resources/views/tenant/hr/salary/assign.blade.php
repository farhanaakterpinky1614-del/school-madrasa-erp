@extends('layouts.app')

@section('title', __('ui.assign_salary'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.assign_salary') }}</h1>
    </div>
@endsection

@section('content')
<form method="POST" action="{{ route('tenant.salary.assign.store') }}" class="max-w-xl space-y-4">@csrf
    <x-ui.select name="staff_id" :label="__('ui.nav.staff')" required>
        <option value="">—</option>
        @foreach ($staff as $member)
            <option value="{{ $member->id }}">{{ $member->full_name }} ({{ $member->employee_code }})</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="salary_structure_id" :label="__('ui.nav.salary')" required>
        <option value="">—</option>
        @foreach ($structures as $structure)
            <option value="{{ $structure->id }}">{{ $structure->name }} — ৳ {{ number_format($structure->net(), 2) }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.input name="effective_from" type="date" :label="__('ui.effective_from')" :value="old('effective_from', now()->toDateString())" required />
    <x-ui.input name="basic_override" type="number" step="0.01" min="0" :label="__('ui.basic_override')" :value="old('basic_override')" />
    <div class="flex gap-3"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        <a href="{{ route('tenant.salary.index') }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a></div>
</form>
@endsection
