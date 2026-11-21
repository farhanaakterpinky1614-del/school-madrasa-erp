@extends('layouts.app')

@section('title', __('ui.nav.salary'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $structure->name }}</h1>
    </div>
@endsection

@section('content')
<form method="POST" action="{{ route('tenant.salary.update', $structure) }}" class="max-w-2xl space-y-4">@csrf @method('PUT')
    @include('tenant.hr.salary._form', ['structure' => $structure])
    <div class="flex gap-3"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        <a href="{{ route('tenant.salary.index') }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a></div>
</form>
@endsection
