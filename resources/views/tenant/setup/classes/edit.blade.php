@extends('layouts.app')

@section('title', __('ui.nav.classes'))

@section('page-header')
    <div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.classes') }}</h1></div>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.classes.update', $class) }}" class="max-w-2xl space-y-4">
        @csrf
        @method('PUT')
        <x-ui.form-section :title="__('ui.nav.classes')">
            <div class="space-y-4">
                <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name', $class->name)" required />
                <x-ui.input name="code" :label="__('ui.code')" :value="old('code', $class->code)" />
                <x-ui.select name="department_id" :label="__('ui.nav.departments')">
                    <option value="">{{ __('ui.select_option') }}</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(old('department_id', $class->department_id) == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="numeric_level" type="number" :label="__('ui.numeric_level')" :value="old('numeric_level', $class->numeric_level)" />
                <x-ui.input name="sort_order" type="number" :label="__('ui.sort_order')" :value="old('sort_order', $class->sort_order)" />
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $class->is_active))> {{ __('ui.status_active') }}</label>
            </div>
            <div class="mt-6 flex gap-3">
                <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
                <a href="{{ route('tenant.classes.index') }}" class="inline-flex items-center text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
            </div>
        </x-ui.form-section>
    </form>
@endsection
