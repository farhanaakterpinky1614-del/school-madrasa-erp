@extends('layouts.app')
@section('title', __('ui.nav.students'))
@section('page-header')
    <div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.edit') }} — {{ $student->full_name }}</h1></div>
@endsection
@section('content')
<form method="POST" action="{{ route('tenant.students.update', $student) }}" class="max-w-3xl space-y-4">
    @csrf @method('PUT')
    <x-ui.form-section :title="__('ui.student_profile')">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.input name="first_name" :label="__('ui.first_name')" :value="old('first_name', $student->first_name)" required />
            <x-ui.input name="last_name" :label="__('ui.last_name')" :value="old('last_name', $student->last_name)" />
            <x-ui.input name="name_bn" :label="__('ui.name_bn')" :value="old('name_bn', $student->name_bn)" />
            <x-ui.select name="gender" :label="__('ui.gender')">
                <option value="">—</option>
                @foreach(['male','female','other'] as $g)
                    <option value="{{ $g }}" @selected(old('gender', $student->gender)===$g)>{{ __('ui.gender_'.$g) }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input name="date_of_birth" type="date" :label="__('ui.date_of_birth')" :value="old('date_of_birth', optional($student->date_of_birth)->format('Y-m-d'))" />
            <x-ui.input name="admission_number" :label="__('ui.admission_number')" :value="old('admission_number', $student->admission_number)" />
            <x-ui.input name="phone" :label="__('ui.phone')" :value="old('phone', $student->phone)" />
            <x-ui.input name="email" type="email" :label="__('ui.field_email')" :value="old('email', $student->email)" />
            <x-ui.input name="blood_group" :label="__('ui.blood_group')" :value="old('blood_group', $student->blood_group)" />
            <x-ui.input name="city" :label="__('ui.city')" :value="old('city', $student->city)" />
            <x-ui.input name="division" :label="__('ui.division')" :value="old('division', $student->division)" />
            <x-ui.select name="status" :label="__('ui.col_status')">
                @foreach(['active','withdrawn','alumni','transferred'] as $st)
                    <option value="{{ $st }}" @selected(old('status', $student->status)===$st)>{{ __('ui.student_status_'.$st) }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <x-ui.textarea name="address" :label="__('ui.address')" class="mt-4">{{ old('address', $student->address) }}</x-ui.textarea>
        <x-ui.textarea name="notes" :label="__('ui.notes')" class="mt-4">{{ old('notes', $student->notes) }}</x-ui.textarea>
        <div class="mt-6 flex gap-3">
            <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
            <a href="{{ route('tenant.students.show', $student) }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
        </div>
    </x-ui.form-section>
</form>
@endsection
