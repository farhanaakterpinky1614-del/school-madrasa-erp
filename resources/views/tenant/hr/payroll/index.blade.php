@extends('layouts.app')

@section('title', __('ui.nav.payroll'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.payroll') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.payroll_help') }}</p>
    </div>
    <a href="{{ route('tenant.payroll.create') }}"><x-ui.button type="button">{{ __('ui.new_payroll_run') }}</x-ui.button></a>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <x-ui.table :headers="[__('ui.period_year'), __('ui.period_month'), __('ui.col_status'), __('ui.payslips'), __('ui.col_action')]">
        @forelse ($runs as $run)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $run->period_year }}</td>
                <td class="px-4 py-3">{{ str_pad((string) $run->period_month, 2, '0', STR_PAD_LEFT) }}</td>
                <td class="px-4 py-3"><x-ui.badge :tone="$run->status === 'paid' ? 'success' : ($run->status === 'processed' ? 'warning' : 'neutral')">{{ ucfirst($run->status) }}</x-ui.badge></td>
                <td class="px-4 py-3">{{ $run->payslips_count }}</td>
                <td class="px-4 py-3"><a href="{{ route('tenant.payroll.show', $run) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.view') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $runs->links() }}</div>
@endsection
