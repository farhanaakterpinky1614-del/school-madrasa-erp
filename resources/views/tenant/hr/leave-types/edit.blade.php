@extends('layouts.app')

@section('title', __('ui.nav.leave_types'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $leaveType->name }}</h1>
    </div>
@endsection

@section('content')
<form method="POST" action="{{ route('tenant.leave-types.update', $leaveType) }}" class="max-w-xl space-y-4">@csrf @method('PUT')
    <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name', $leaveType->name)" required />
    <x-ui.input name="code" :label="__('ui.code')" :value="old('code', $leaveType->code)" />
    <x-ui.input name="days_per_year" type="number" min="0" :label="__('ui.days_per_year')" :value="old('days_per_year', $leaveType->days_per_year)" required />
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_paid" value="1" @checked(old('is_paid', $leaveType->is_paid)) /> {{ __('ui.is_paid') }}</label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="requires_approval" value="1" @checked(old('requires_approval', $leaveType->requires_approval)) /> {{ __('ui.requires_approval') }}</label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $leaveType->is_active)) /> {{ __('ui.staff_status_active') }}</label>
    <div class="flex gap-3"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        <a href="{{ route('tenant.leave-types.index') }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a></div>
</form>
@endsection
