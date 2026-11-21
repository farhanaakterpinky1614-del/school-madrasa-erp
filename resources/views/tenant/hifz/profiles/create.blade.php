@extends('layouts.app')

@section('title', __('ui.new_hifz_profile'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 10</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.new_hifz_profile') }}</h1>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.hifz.profiles.store') }}" class="max-w-3xl space-y-4">
        @csrf
        @include('tenant.hifz.profiles._form')
        <div class="flex gap-3">
            <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
            <a href="{{ route('tenant.hifz.profiles.index') }}" class="inline-flex items-center text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
        </div>
    </form>
@endsection
