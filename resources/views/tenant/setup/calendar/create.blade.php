@extends('layouts.app')

@section('title', __('ui.nav.calendar'))

@section('page-header')
    <div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.calendar') }}</h1></div>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.calendar.store') }}" class="max-w-2xl space-y-4">
        @csrf
        <x-ui.form-section :title="__('ui.nav.calendar')">
            <div class="space-y-4">
                <x-ui.input name="title" :label="__('ui.col_name')" :value="old('title')" required />
                <x-ui.select name="event_type" :label="__('ui.event_type')">
                    @foreach (['holiday','working','exam','event'] as $type)
                        <option value="{{ $type }}" @selected(old('event_type', 'holiday') === $type)>{{ __('ui.event_type_'.$type) }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="academic_year_id" :label="__('ui.nav.years')">
                    <option value="">{{ __('ui.select_option') }}</option>
                    @foreach ($years as $year)
                        <option value="{{ $year->id }}" @selected(old('academic_year_id') == $year->id)>{{ $year->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="starts_on" type="date" :label="__('ui.starts_on')" :value="old('starts_on')" required />
                <x-ui.input name="ends_on" type="date" :label="__('ui.ends_on')" :value="old('ends_on')" />
                <x-ui.textarea name="description" :label="__('ui.description')">{{ old('description') }}</x-ui.textarea>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_recurring" value="1" @checked(old('is_recurring'))> {{ __('ui.recurring') }}</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> {{ __('ui.status_active') }}</label>
            </div>
            <div class="mt-6 flex gap-3">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
                <a href="{{ route('tenant.calendar.index') }}" class="inline-flex items-center text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
            </div>
        </x-ui.form-section>
    </form>
@endsection
