@extends('layouts.app')

@section('title', __('ui.nav.leave_requests'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $leaveRequest->staff?->full_name }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ $leaveRequest->leaveType?->name }} · {{ __('ui.leave_status_'.$leaveRequest->status) }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <section class="max-w-2xl space-y-4 rounded-2xl border border-slate-200/80 bg-white/70 p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900/40">
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-muted">{{ __('ui.starts_on') }}</dt><dd class="font-medium">{{ optional($leaveRequest->starts_on)->format('Y-m-d') }}</dd></div>
            <div><dt class="text-muted">{{ __('ui.ends_on') }}</dt><dd class="font-medium">{{ optional($leaveRequest->ends_on)->format('Y-m-d') }}</dd></div>
            <div><dt class="text-muted">Days</dt><dd class="font-medium">{{ $leaveRequest->days }}</dd></div>
            <div><dt class="text-muted">{{ __('ui.col_status') }}</dt><dd class="font-medium">{{ __('ui.leave_status_'.$leaveRequest->status) }}</dd></div>
        </dl>
        <p class="text-sm"><span class="text-muted">{{ __('ui.reason') }}:</span> {{ $leaveRequest->reason ?: '—' }}</p>
        @if ($leaveRequest->status === 'pending')
            <form method="POST" action="{{ route('tenant.leave-requests.approve', $leaveRequest) }}" class="space-y-3">@csrf
                <x-ui.textarea name="review_notes" :label="__('ui.review_notes')" />
                <div class="flex flex-wrap gap-2">
                    <x-ui.button type="submit">{{ __('ui.approve') }}</x-ui.button>
                    <button formaction="{{ route('tenant.leave-requests.reject', $leaveRequest) }}" class="inline-flex items-center rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700">{{ __('ui.reject') }}</button>
                </div>
            </form>
        @elseif ($leaveRequest->review_notes)
            <p class="text-sm text-muted">{{ __('ui.review_notes') }}: {{ $leaveRequest->review_notes }}</p>
        @endif
    </section>
@endsection
