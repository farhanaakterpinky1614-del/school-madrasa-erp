@extends('layouts.app')

@section('title', __('ui.nav.shifts'))

@section('page-header')
    <div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.shifts') }}</h1></div>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.shifts.store') }}" class="max-w-2xl space-y-4">
        @csrf
        <x-ui.form-section :title="__('ui.nav.shifts')">
            <div class="space-y-4">
                <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name')" required />
                <x-ui.input name="code" :label="__('ui.code')" :value="old('code')" />
                <x-ui.input name="starts_at" type="time" :label="__('ui.starts_at')" :value="old('starts_at')" />
                <x-ui.input name="ends_at" type="time" :label="__('ui.ends_at')" :value="old('ends_at')" />
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> {{ __('ui.status_active') }}</label>
            </div>
            <div class="mt-6 flex gap-3">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
                <a href="{{ route('tenant.shifts.index') }}" class="inline-flex items-center text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
            </div>
        </x-ui.form-section>
    </form>
@endsection
