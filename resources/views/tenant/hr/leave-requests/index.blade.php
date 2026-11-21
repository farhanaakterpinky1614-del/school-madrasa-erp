@extends('layouts.app')

@section('title', __('ui.nav.leave_requests'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.leave_requests') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.leave_requests_help') }}</p>
    </div>
    <a href="{{ route('tenant.leave-requests.create') }}"><x-ui.button type="button">{{ __('ui.new_leave_request') }}</x-ui.button></a>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <x-ui.select name="status" :label="__('ui.col_status')">
            <option value="">{{ __('ui.all') }}</option>
            @foreach (['pending','approved','rejected','cancelled'] as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ __('ui.leave_status_'.$st) }}</option>
            @endforeach
        </x-ui.select>
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button></div>
    </form>
    <x-ui.table :headers="[__('ui.nav.staff'), __('ui.nav.leave_types'), __('ui.starts_on'), __('ui.ends_on'), __('ui.col_status'), __('ui.col_action')]">
        @forelse ($requests as $req)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $req->staff?->full_name }}</td>
                <td class="px-4 py-3 text-muted">{{ $req->leaveType?->name }}</td>
                <td class="px-4 py-3">{{ optional($req->starts_on)->format('Y-m-d') }}</td>
                <td class="px-4 py-3">{{ optional($req->ends_on)->format('Y-m-d') }}</td>
                <td class="px-4 py-3"><x-ui.badge :tone="$req->status === 'approved' ? 'success' : ($req->status === 'pending' ? 'warning' : 'neutral')">{{ __('ui.leave_status_'.$req->status) }}</x-ui.badge></td>
                <td class="px-4 py-3"><a href="{{ route('tenant.leave-requests.show', $req) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.view') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $requests->links() }}</div>
@endsection
