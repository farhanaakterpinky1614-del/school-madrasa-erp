@extends('layouts.app')

@section('title', __('ui.nav.years'))

@section('page-header')
    <div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.years') }}</h1></div>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.years.update', $year) }}" class="max-w-2xl space-y-4">
        @csrf
        @method('PUT')
        <x-ui.form-section :title="__('ui.nav.years')">
            <div class="space-y-4">
                <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name', $year->name)" required />
                <x-ui.input name="code" :label="__('ui.code')" :value="old('code', $year->code)" />
                <x-ui.input name="starts_on" type="date" :label="__('ui.starts_on')" :value="old('starts_on', optional($year->starts_on)->format('Y-m-d'))" required />
                <x-ui.input name="ends_on" type="date" :label="__('ui.ends_on')" :value="old('ends_on', optional($year->ends_on)->format('Y-m-d'))" required />
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_current" value="1" @checked(old('is_current', $year->is_current))> {{ __('ui.current') }}</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $year->is_active))> {{ __('ui.status_active') }}</label>
            </div>
            <div class="mt-6 flex gap-3">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
                <a href="{{ route('tenant.years.index') }}" class="inline-flex items-center text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
            </div>
        </x-ui.form-section>
    </form>
@endsection
