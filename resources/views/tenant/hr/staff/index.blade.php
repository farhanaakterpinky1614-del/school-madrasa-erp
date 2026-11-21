@extends('layouts.app')

@section('title', __('ui.nav.staff'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.staff') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.staff_help') }}</p>
    </div>
    <a href="{{ route('tenant.staff.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <x-ui.input name="q" :label="__('ui.search')" :value="request('q')" class="min-w-[14rem]" />
        <x-ui.select name="type" :label="__('ui.staff_type')">
            <option value="">{{ __('ui.all') }}</option>
            <option value="teacher" @selected(request('type') === 'teacher')>{{ __('ui.staff_type_teacher') }}</option>
            <option value="employee" @selected(request('type') === 'employee')>{{ __('ui.staff_type_employee') }}</option>
        </x-ui.select>
        <x-ui.select name="status" :label="__('ui.col_status')">
            <option value="">{{ __('ui.all') }}</option>
            @foreach (['active','inactive','terminated'] as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ __('ui.staff_status_'.$st) }}</option>
            @endforeach
        </x-ui.select>
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button></div>
    </form>
    <x-ui.table :headers="[__('ui.employee_code'), __('ui.col_name'), __('ui.designation'), __('ui.department'), __('ui.col_status'), __('ui.col_action')]">
        @forelse ($staff as $member)
            <tr>
                <td class="px-4 py-3 font-mono text-sm">{{ $member->employee_code }}</td>
                <td class="px-4 py-3 font-medium">{{ $member->full_name }}</td>
                <td class="px-4 py-3 text-muted">{{ $member->designation?->name ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $member->department?->name ?: '—' }}</td>
                <td class="px-4 py-3"><x-ui.badge :tone="$member->status === 'active' ? 'success' : 'warning'">{{ __('ui.staff_status_'.$member->status) }}</x-ui.badge></td>
                <td class="px-4 py-3"><a href="{{ route('tenant.staff.show', $member) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.view') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $staff->links() }}</div>
@endsection
