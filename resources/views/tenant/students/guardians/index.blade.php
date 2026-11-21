@extends('layouts.app')
@section('title', __('ui.nav.guardians'))
@section('page-header')
<div>
    <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 05</p>
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.guardians') }}</h1>
</div>
<a href="{{ route('tenant.guardians.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
@endsection
@section('content')
@if (session('status'))<p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>@endif
<x-ui.table :headers="[__('ui.col_name'), __('ui.phone'), __('ui.students_count'), __('ui.col_action')]">
@forelse($guardians as $guardian)
<tr>
<td class="px-4 py-3 font-medium">{{ $guardian->name }}</td>
<td class="px-4 py-3 text-muted">{{ $guardian->phone ?: '—' }}</td>
<td class="px-4 py-3">{{ $guardian->students_count }}</td>
<td class="px-4 py-3"><a href="{{ route('tenant.guardians.edit', $guardian) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.edit') }}</a></td>
</tr>
@empty
<tr><td colspan="4" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
@endforelse
</x-ui.table>
<div class="mt-4">{{ $guardians->links() }}</div>
@endsection
