@extends('layouts.app')

@section('title', __('ui.nav.staff'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $staff->full_name }}</h1>
    </div>
@endsection

@section('content')
<form method="POST" action="{{ route('tenant.staff.update', $staff) }}" class="max-w-3xl space-y-4">@csrf @method('PUT')
    @include('tenant.hr.staff._form', ['staff' => $staff])
    <div class="flex items-center gap-3">
        <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        <a href="{{ route('tenant.staff.show', $staff) }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
    </div>
</form>
@endsection
