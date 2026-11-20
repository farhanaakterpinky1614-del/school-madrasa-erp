@extends('layouts.app')

@section('title', __('ui.nav.departments'))

@section('page-header')
    <div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.departments') }}</h1></div>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.departments.store') }}" class="max-w-2xl space-y-4">
        @csrf
        <x-ui.form-section :title="__('ui.nav.departments')">
            <div class="space-y-4">
                <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name')" required />
                <x-ui.input name="code" :label="__('ui.code')" :value="old('code')" />
                <x-ui.select name="branch_id" :label="__('ui.nav.branches')">
                    <option value="">{{ __('ui.select_option') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="kind" :label="__('ui.kind')">
                    <option value="academic" @selected(old('kind', 'academic') === 'academic')">{{ __('ui.kind_academic') }}</option>
                    <option value="administrative" @selected(old('kind') === 'administrative')">{{ __('ui.kind_administrative') }}</option>
                </x-ui.select>
                <x-ui.textarea name="description" :label="__('ui.description')">{{ old('description') }}</x-ui.textarea>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> {{ __('ui.status_active') }}</label>
            </div>
            <div class="mt-6 flex gap-3">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
                <a href="{{ route('tenant.departments.index') }}" class="inline-flex items-center text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
            </div>
        </x-ui.form-section>
    </form>
@endsection
