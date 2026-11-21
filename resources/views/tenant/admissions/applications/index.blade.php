@extends('layouts.app')
@section('title', __('ui.nav.applications'))
@section('page-header')
<div>
<p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 05</p>
<h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.applications') }}</h1>
</div>
<a href="{{ route('tenant.applications.offline.create') }}"><x-ui.button type="button">{{ __('ui.offline_admission') }}</x-ui.button></a>
@endsection
@section('content')
@if (session('status'))<p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>@endif
<form method="GET" class="mb-4 flex flex-wrap gap-3">
<x-ui.select name="status" :label="__('ui.col_status')">
<option value="">{{ __('ui.all') }}</option>
@foreach(['submitted','under_review','approved','rejected','enrolled'] as $st)
<option value="{{ $st }}" @selected(request('status')===$st)>{{ __('ui.app_status_'.$st) }}</option>
@endforeach
</x-ui.select>
<div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button></div>
</form>
<x-ui.table :headers="[__('ui.application_number'), __('ui.col_name'), __('ui.channel'), __('ui.col_status'), __('ui.col_action')]">
@forelse($applications as $app)
<tr>
<td class="px-4 py-3 font-mono text-sm">{{ $app->application_number }}</td>
<td class="px-4 py-3 font-medium">{{ $app->full_name }}</td>
<td class="px-4 py-3 text-muted">{{ $app->channel }}</td>
<td class="px-4 py-3"><x-ui.badge tone="info">{{ __('ui.app_status_'.$app->status) }}</x-ui.badge></td>
<td class="px-4 py-3"><a href="{{ route('tenant.applications.show', $app) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.view') }}</a></td>
</tr>
@empty
<tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
@endforelse
</x-ui.table>
<div class="mt-4">{{ $applications->links() }}</div>
@endsection
