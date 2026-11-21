@extends('layouts.app')
@section('title', __('ui.nav.admission_sessions'))
@section('page-header')
<div>
<p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 05</p>
<h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.admission_sessions') }}</h1>
</div>
<a href="{{ route('tenant.admission-sessions.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
@endsection
@section('content')
@if (session('status'))<p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>@endif
<x-ui.table :headers="[__('ui.col_name'), __('ui.nav.years'), __('ui.applications'), __('ui.online_link'), __('ui.col_action')]">
@forelse($sessions as $session)
<tr>
<td class="px-4 py-3 font-medium">{{ $session->name }}</td>
<td class="px-4 py-3 text-muted">{{ $session->academicYear?->name }}</td>
<td class="px-4 py-3">{{ $session->applications_count }}</td>
<td class="px-4 py-3 text-xs">
@if($session->allow_online)
<a class="font-semibold text-primary-600" href="{{ route('public.admissions.show', [app(\App\Core\Tenancy\CurrentTenant::class)->get()?->slug, $session->public_slug]) }}" target="_blank">/apply/.../{{ $session->public_slug }}</a>
@else — @endif
</td>
<td class="px-4 py-3"><a href="{{ route('tenant.admission-sessions.edit', $session) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.edit') }}</a></td>
</tr>
@empty
<tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
@endforelse
</x-ui.table>
<div class="mt-4">{{ $sessions->links() }}</div>
@endsection
