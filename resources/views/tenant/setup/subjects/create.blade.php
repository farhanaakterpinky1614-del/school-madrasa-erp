@extends('layouts.app')

@section('title', __('ui.nav.subjects'))

@section('page-header')
    <div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.subjects') }}</h1></div>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.subjects.store') }}" class="max-w-2xl space-y-4">
        @csrf
        <x-ui.form-section :title="__('ui.nav.subjects')">
            <div class="space-y-4">
                <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name')" required />
                <x-ui.input name="name_bn" :label="__('ui.name_bn')" :value="old('name_bn')" />
                <x-ui.input name="name_ar" :label="__('ui.name_ar')" :value="old('name_ar')" />
                <x-ui.input name="code" :label="__('ui.code')" :value="old('code')" />
                <x-ui.select name="subject_type" :label="__('ui.subject_type')">
                    @foreach (['general','islamic','hifz','optional'] as $type)
                        <option value="{{ $type }}" @selected(old('subject_type', 'general') === $type)>{{ __('ui.subject_type_'.$type) }}</option>
                    @endforeach
                </x-ui.select>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> {{ __('ui.status_active') }}</label>
            </div>
            <div class="mt-6 flex gap-3">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
                <a href="{{ route('tenant.subjects.index') }}" class="inline-flex items-center text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
            </div>
        </x-ui.form-section>
    </form>
@endsection
