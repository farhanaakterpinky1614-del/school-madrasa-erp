@extends('layouts.app')
@section('title', __('ui.nav.students'))
@section('page-header')
    <div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.register_student') }}</h1></div>
@endsection
@section('content')
<form method="POST" action="{{ route('tenant.students.store') }}" class="max-w-3xl space-y-4">
    @csrf
    <x-ui.form-section :title="__('ui.student_profile')">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.input name="first_name" :label="__('ui.first_name')" :value="old('first_name')" required />
            <x-ui.input name="last_name" :label="__('ui.last_name')" :value="old('last_name')" />
            <x-ui.input name="name_bn" :label="__('ui.name_bn')" :value="old('name_bn')" />
            <x-ui.select name="gender" :label="__('ui.gender')">
                <option value="">—</option>
                @foreach(['male','female','other'] as $g)
                    <option value="{{ $g }}" @selected(old('gender')===$g)>{{ __('ui.gender_'.$g) }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input name="date_of_birth" type="date" :label="__('ui.date_of_birth')" :value="old('date_of_birth')" />
            <x-ui.input name="admission_number" :label="__('ui.admission_number')" :value="old('admission_number')" />
            <x-ui.input name="phone" :label="__('ui.phone')" :value="old('phone')" />
            <x-ui.input name="email" type="email" :label="__('ui.field_email')" :value="old('email')" />
            <x-ui.input name="city" :label="__('ui.city')" :value="old('city')" />
            <x-ui.input name="division" :label="__('ui.division')" :value="old('division')" />
        </div>
        <x-ui.textarea name="address" :label="__('ui.address')" class="mt-4">{{ old('address') }}</x-ui.textarea>
        <x-ui.textarea name="notes" :label="__('ui.notes')" class="mt-4">{{ old('notes') }}</x-ui.textarea>
        <div class="mt-6 flex gap-3">
            <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
            <a href="{{ route('tenant.students.index') }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
        </div>
    </x-ui.form-section>
</form>
@endsection
