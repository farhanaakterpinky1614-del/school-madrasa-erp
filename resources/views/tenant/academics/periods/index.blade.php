@extends('layouts.app')
@section('title', __('ui.nav.periods'))
@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 07</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.periods') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.periods_help') }}</p>
    </div>
    <a href="{{ route('tenant.periods.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
@endsection
@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <x-ui.table :headers="[__('ui.col_name'), __('ui.code'), __('ui.sort_order'), __('ui.time'), __('ui.nav.shifts'), __('ui.col_action')]">
        @forelse ($periods as $period)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $period->name }} @if($period->is_break)<x-ui.badge tone="warning">{{ __('ui.break') }}</x-ui.badge>@endif</td>
                <td class="px-4 py-3 text-muted">{{ $period->code ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $period->sort_order }}</td>
                <td class="px-4 py-3 text-muted">{{ $period->starts_at ? \Illuminate\Support\Str::of($period->starts_at)->substr(0,5) : '—' }}–{{ $period->ends_at ? \Illuminate\Support\Str::of($period->ends_at)->substr(0,5) : '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $period->shift?->name ?: '—' }}</td>
                <td class="px-4 py-3"><a href="{{ route('tenant.periods.edit', $period) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.edit') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $periods->links() }}</div>
@endsection
