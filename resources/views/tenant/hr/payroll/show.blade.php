@extends('layouts.app')

@section('title', __('ui.nav.payroll'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $run->periodLabel() }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ ucfirst($run->status) }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if ($run->status === 'draft')
            <form method="POST" action="{{ route('tenant.payroll.process', $run) }}">@csrf
                <x-ui.button type="submit">{{ __('ui.process_payroll') }}</x-ui.button>
            </form>
        @endif
        @if ($run->status === 'processed')
            <form method="POST" action="{{ route('tenant.payroll.paid', $run) }}">@csrf
                <x-ui.button type="submit" variant="secondary">{{ __('ui.mark_paid') }}</x-ui.button>
            </form>
        @endif
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <x-ui.table :headers="[__('ui.nav.staff'), __('ui.basic_salary'), __('ui.allowances'), __('ui.deductions'), __('ui.net_pay'), __('ui.col_status')]">
        @forelse ($run->payslips as $slip)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $slip->staff?->full_name }}</td>
                <td class="px-4 py-3">৳ {{ number_format($slip->basic, 2) }}</td>
                <td class="px-4 py-3">৳ {{ number_format($slip->allowances, 2) }}</td>
                <td class="px-4 py-3">৳ {{ number_format($slip->deductions, 2) }}</td>
                <td class="px-4 py-3 font-semibold">৳ {{ number_format($slip->net_pay, 2) }}</td>
                <td class="px-4 py-3">{{ ucfirst($slip->status) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
@endsection
