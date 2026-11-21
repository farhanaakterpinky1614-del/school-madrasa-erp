@extends('layouts.app')

@section('title', __('ui.new_leave_request'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.new_leave_request') }}</h1>
    </div>
@endsection

@section('content')
<form method="POST" action="{{ route('tenant.leave-requests.store') }}" class="max-w-xl space-y-4">@csrf
    <x-ui.select name="staff_id" :label="__('ui.nav.staff')" required>
        <option value="">—</option>
        @foreach ($staff as $member)
            <option value="{{ $member->id }}" @selected((int) old('staff_id') === $member->id)>{{ $member->full_name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="leave_type_id" :label="__('ui.nav.leave_types')" required>
        <option value="">—</option>
        @foreach ($leaveTypes as $type)
            <option value="{{ $type->id }}" @selected((int) old('leave_type_id') === $type->id)>{{ $type->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.input name="starts_on" type="date" :label="__('ui.starts_on')" :value="old('starts_on')" required />
    <x-ui.input name="ends_on" type="date" :label="__('ui.ends_on')" :value="old('ends_on')" required />
    <x-ui.textarea name="reason" :label="__('ui.reason')" :value="old('reason')" />
    <div class="flex gap-3"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        <a href="{{ route('tenant.leave-requests.index') }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a></div>
</form>
@endsection
