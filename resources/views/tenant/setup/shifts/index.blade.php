@extends('layouts.app')

@section('title', __('ui.nav.shifts'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 04</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.shifts') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.shifts_help') }}</p>
    </div>
    <a href="{{ route('tenant.shifts.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <x-ui.table :headers="[__('ui.col_name'), __('ui.code'), __('ui.starts_at'), __('ui.ends_at'), __('ui.col_action')]">
        @forelse ($shifts as $shift)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $shift->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $shift->code ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $shift->starts_at ? substr($shift->starts_at, 0, 5) : '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $shift->ends_at ? substr($shift->ends_at, 0, 5) : '—' }}</td>
                <td class="px-4 py-3"><a href="{{ route('tenant.shifts.edit', $shift) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.edit') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $shifts->links() }}</div>
@endsection
