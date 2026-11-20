@extends('layouts.app')

@section('title', __('ui.nav.grading'))

@section('page-header')
    <div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.grading') }}</h1></div>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.grading.store') }}" class="max-w-2xl space-y-4">
        @csrf
        <x-ui.form-section :title="__('ui.nav.grading')">
            <div class="space-y-4">
                <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name')" required />
                <x-ui.input name="code" :label="__('ui.code')" :value="old('code')" />
                <x-ui.textarea name="description" :label="__('ui.description')">{{ old('description') }}</x-ui.textarea>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1" @checked(old('is_default'))> {{ __('ui.default') }}</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> {{ __('ui.status_active') }}</label>
            </div>
            <div class="mt-6 flex gap-3">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
                <a href="{{ route('tenant.grading.index') }}" class="inline-flex items-center text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
            </div>
        </x-ui.form-section>
    </form>
@endsection
