@extends('layouts.app')

@section('title', __('ui.nav.hostel_allocations'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 13</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.hostel_allocations') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.hostel_allocations_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
            <ul class="list-disc ps-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.status') }}</label>
            <select name="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all') }}</option>
                @foreach ($allocationStatuses as $st)
                    <option value="{{ $st }}" @selected($filterStatus === $st)>{{ __('ui.allocation_status_'.$st) }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    @can('hostel.allocation.manage')
        <form method="POST" action="{{ route('tenant.hostel.allocations.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-3">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.student') }}</label>
                <select name="student_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    <option value="">{{ __('ui.select') }}</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->student_code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.bed') }}</label>
                <select name="hostel_bed_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    <option value="">{{ __('ui.select') }}</option>
                    @foreach ($availableBeds as $bed)
                        <option value="{{ $bed->id }}">
                            {{ $bed->room?->hostel?->name }} · {{ __('ui.room') }} {{ $bed->room?->room_number }} · {{ $bed->label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.check_in_on') }}</label>
                <input type="date" name="check_in_on" value="{{ old('check_in_on', now('Asia/Dhaka')->toDateString()) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.fees_note') }}</label>
                <input name="fees_note" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" placeholder="{{ __('ui.fees_note_placeholder') }}" value="{{ old('fees_note') }}">
            </div>
            <div class="flex items-end">
                <x-ui.button type="submit">{{ __('ui.allocate_bed') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <x-ui.table :headers="[__('ui.student'), __('ui.hostel'), __('ui.room'), __('ui.bed'), __('ui.check_in_on'), __('ui.check_out_on'), __('ui.status'), __('ui.col_action')]">
        @forelse ($allocations as $allocation)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $allocation->student?->full_name ?: '—' }}</td>
                <td class="px-4 py-3">{{ $allocation->bed?->room?->hostel?->name ?: '—' }}</td>
                <td class="px-4 py-3">{{ $allocation->bed?->room?->room_number ?: '—' }}</td>
                <td class="px-4 py-3">{{ $allocation->bed?->label ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ optional($allocation->check_in_on)->toDateString() }}</td>
                <td class="px-4 py-3 text-muted">{{ optional($allocation->check_out_on)->toDateString() ?: '—' }}</td>
                <td class="px-4 py-3">{{ __('ui.allocation_status_'.$allocation->status) }}</td>
                <td class="px-4 py-3">
                    @can('hostel.allocation.manage')
                        @if ($allocation->status === 'active')
                            <form method="POST" action="{{ route('tenant.hostel.allocations.checkout', $allocation) }}">
                                @csrf
                                <button type="submit" class="text-sm font-semibold text-amber-700">{{ __('ui.check_out') }}</button>
                            </form>
                        @else
                            —
                        @endif
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $allocations->links() }}</div>
@endsection
