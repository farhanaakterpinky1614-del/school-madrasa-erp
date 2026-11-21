@extends('layouts.app')

@section('title', __('ui.nav.salary'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.salary') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.salary_help') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('tenant.salary.assign') }}"><x-ui.button type="button" variant="outline">{{ __('ui.assign_salary') }}</x-ui.button></a>
        <a href="{{ route('tenant.salary.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <x-ui.table :headers="[__('ui.col_name'), __('ui.code'), __('ui.basic_salary'), __('ui.net_pay'), __('ui.col_action')]">
        @forelse ($structures as $structure)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $structure->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $structure->code ?: '—' }}</td>
                <td class="px-4 py-3">৳ {{ number_format($structure->basic_salary, 2) }}</td>
                <td class="px-4 py-3 font-semibold">৳ {{ number_format($structure->net(), 2) }}</td>
                <td class="px-4 py-3"><a href="{{ route('tenant.salary.edit', $structure) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.edit') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $structures->links() }}</div>
@endsection
