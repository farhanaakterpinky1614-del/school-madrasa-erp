@extends('layouts.app')

@section('title', __('ui.nav.staff_attendance'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.staff_attendance') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.staff_attendance_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <x-ui.input name="date" type="date" :label="__('ui.attendance_date')" :value="$date" />
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button></div>
    </form>
    <form method="POST" action="{{ route('tenant.staff-attendance.store') }}" class="space-y-4">@csrf
        <input type="hidden" name="attendance_date" value="{{ $date }}" />
        <x-ui.table :headers="[__('ui.col_name'), __('ui.col_status'), __('ui.check_in'), __('ui.notes')]">
            @foreach ($staff as $i => $member)
                @php $row = $existing->get($member->id); @endphp
                <tr>
                    <td class="px-4 py-3 font-medium">
                        {{ $member->full_name }}
                        <input type="hidden" name="entries[{{ $i }}][staff_id]" value="{{ $member->id }}" />
                    </td>
                    <td class="px-4 py-3">
                        <select name="entries[{{ $i }}][status]" class="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-900">
                            @foreach (['present','absent','late','half_day','on_leave'] as $st)
                                <option value="{{ $st }}" @selected(($row?->status ?? 'present') === $st)>{{ __('ui.att_'.$st) }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-4 py-3">
                        <input type="time" name="entries[{{ $i }}][check_in]" value="{{ $row?->check_in ? \Illuminate\Support\Str::of($row->check_in)->substr(0,5) : '' }}" class="rounded-lg border border-slate-200 px-2 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-900" />
                    </td>
                    <td class="px-4 py-3">
                        <input type="text" name="entries[{{ $i }}][notes]" value="{{ $row?->notes ?? '' }}" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-900" />
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <x-ui.button type="submit">{{ __('ui.mark_attendance') }}</x-ui.button>
    </form>
@endsection
