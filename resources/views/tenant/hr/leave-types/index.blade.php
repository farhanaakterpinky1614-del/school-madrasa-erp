@extends('layouts.app')

@section('title', __('ui.nav.leave_types'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.leave_types') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.leave_types_help') }}</p>
    </div>
    <a href="{{ route('tenant.leave-types.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <x-ui.table :headers="[__('ui.col_name'), __('ui.code'), __('ui.days_per_year'), __('ui.is_paid'), __('ui.col_action')]">
        @forelse ($leaveTypes as $type)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $type->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $type->code ?: '—' }}</td>
                <td class="px-4 py-3">{{ $type->days_per_year }}</td>
                <td class="px-4 py-3">{{ $type->is_paid ? __('ui.yes') : '—' }}</td>
                <td class="px-4 py-3"><a href="{{ route('tenant.leave-types.edit', $type) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.edit') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $leaveTypes->links() }}</div>
@endsection
