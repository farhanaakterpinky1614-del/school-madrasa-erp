@extends('layouts.guest')
@section('title', __('ui.application_received'))
@section('content')
<div class="mx-auto max-w-lg px-4 py-16 text-center">
<p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-600">{{ $tenant->name }}</p>
<h1 class="mt-3 text-3xl font-semibold tracking-tight">{{ __('ui.application_received') }}</h1>
<p class="mt-3 text-sm text-muted">{{ __('ui.application_received_help') }}</p>
<p class="mt-6 rounded-xl border border-sme bg-white px-4 py-3 font-mono text-sm">{{ $application->application_number }}</p>
</div>
@endsection
