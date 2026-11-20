@extends('layouts.app')

@section('title', __('ui.nav.curricula'))

@section('page-header')
    <div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.curricula') }}</h1></div>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.curricula.store') }}" class="max-w-2xl space-y-4">
        @csrf
        <x-ui.form-section :title="__('ui.nav.curricula')">
            <div class="space-y-4">
                <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name')" required />
                <x-ui.input name="code" :label="__('ui.code')" :value="old('code')" />
                <x-ui.input name="board" :label="__('ui.board')" :value="old('board')" />
                <x-ui.textarea name="description" :label="__('ui.description')">{{ old('description') }}</x-ui.textarea>
                <x-ui.select name="subject_id" :label="__('ui.nav.subjects')" :hint="__('ui.optional_first_subject')">
                    <option value="">{{ __('ui.select_option') }}</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="school_class_id" :label="__('ui.nav.classes')">
                    <option value="">{{ __('ui.select_option') }}</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </x-ui.select>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1" @checked(old('is_default'))> {{ __('ui.default') }}</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> {{ __('ui.status_active') }}</label>
            </div>
            <div class="mt-6 flex gap-3">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
                <a href="{{ route('tenant.curricula.index') }}" class="inline-flex items-center text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
            </div>
        </x-ui.form-section>
    </form>
@endsection
