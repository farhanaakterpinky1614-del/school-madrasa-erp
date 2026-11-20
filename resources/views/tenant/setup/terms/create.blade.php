@extends('layouts.app')

@section('title', __('ui.nav.terms'))

@section('page-header')
    <div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.terms') }}</h1></div>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.terms.store') }}" class="max-w-2xl space-y-4">
        @csrf
        <x-ui.form-section :title="__('ui.nav.terms')">
            <div class="space-y-4">
                <x-ui.select name="academic_year_id" :label="__('ui.nav.years')" required>
                    <option value="">{{ __('ui.select_option') }}</option>
                    @foreach ($years as $year)
                        <option value="{{ $year->id }}" @selected(old('academic_year_id') == $year->id)>{{ $year->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name')" required />
                <x-ui.input name="code" :label="__('ui.code')" :value="old('code')" />
                <x-ui.input name="starts_on" type="date" :label="__('ui.starts_on')" :value="old('starts_on')" />
                <x-ui.input name="ends_on" type="date" :label="__('ui.ends_on')" :value="old('ends_on')" />
                <x-ui.input name="sort_order" type="number" :label="__('ui.sort_order')" :value="old('sort_order', 0)" />
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_current" value="1" @checked(old('is_current'))> {{ __('ui.current') }}</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> {{ __('ui.status_active') }}</label>
            </div>
            <div class="mt-6 flex gap-3">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
                <a href="{{ route('tenant.terms.index') }}" class="inline-flex items-center text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
            </div>
        </x-ui.form-section>
    </form>
@endsection
