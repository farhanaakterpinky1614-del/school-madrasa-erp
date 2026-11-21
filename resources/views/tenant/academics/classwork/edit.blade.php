@extends('layouts.app')
@section('title', __('ui.nav.classwork'))
@section('page-header')
    <div><p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 07</p>
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.edit') }}</h1></div>
@endsection
@section('content')
<form method="POST" action="{{ route('tenant.classwork.update', $item) }}" class="max-w-2xl space-y-4">@csrf @method('PUT')
    <x-ui.input name="title" :label="__('ui.col_name')" :value="old('title', $item->title)" required />
    <x-ui.select name="academic_year_id" :label="__('ui.nav.years')">
        <option value="">—</option>
        @foreach ($years as $y)
            <option value="{{ $y->id }}" @selected((int) old('academic_year_id', $item->academic_year_id) === $y->id)>{{ $y->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="school_class_id" :label="__('ui.nav.classes')" required>
        @foreach ($classes as $c)
            <option value="{{ $c->id }}" @selected((int) old('school_class_id', $item->school_class_id) === $c->id)>{{ $c->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="section_id" :label="__('ui.nav.sections')">
        <option value="">—</option>
        @foreach ($sections as $sec)
            <option value="{{ $sec->id }}" @selected((int) old('section_id', $item->section_id) === $sec->id)>{{ $sec->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="subject_id" :label="__('ui.nav.subjects')" required>
        @foreach ($subjects as $sub)
            <option value="{{ $sub->id }}" @selected((int) old('subject_id', $item->subject_id) === $sub->id)>{{ $sub->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="staff_id" :label="__('ui.nav.teachers')">
        <option value="">—</option>
        @foreach ($teachers as $t)
            <option value="{{ $t->id }}" @selected((int) old('staff_id', $item->staff_id) === $t->id)>{{ $t->full_name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.input name="assigned_on" type="date" :label="__('ui.assigned_on')" :value="old('assigned_on', optional($item->assigned_on)->toDateString())" />
    <x-ui.input name="due_on" type="date" :label="__('ui.due_on')" :value="old('due_on', optional($item->due_on)->toDateString())" />
    <x-ui.input name="max_marks" type="number" step="0.01" :label="__('ui.max_marks')" :value="old('max_marks', $item->max_marks)" />
    <x-ui.select name="status" :label="__('ui.col_status')" required>
        @foreach (['draft','published','closed'] as $st)
            <option value="{{ $st }}" @selected(old('status', $item->status) === $st)>{{ __('ui.status_'.$st) }}</option>
        @endforeach
    </x-ui.select>
    <label class="block text-sm font-medium">{{ __('ui.description') }}</label>
    <textarea name="description" rows="3" class="block w-full rounded-xl border border-sme bg-surface/90 px-3 py-2.5 text-sm">{{ old('description', $item->description) }}</textarea>
    <div class="flex gap-3"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        <a href="{{ route('tenant.classwork.index') }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a></div>
</form>
@endsection
