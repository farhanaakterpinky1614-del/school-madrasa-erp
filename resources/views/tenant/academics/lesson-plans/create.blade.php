@extends('layouts.app')
@section('title', __('ui.nav.lesson_plans'))
@section('page-header')
    <div><p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 07</p>
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.new_record') }}</h1></div>
@endsection
@section('content')
@php($item = null)
<form method="POST" action="{{ route('tenant.lesson-plans.store') }}" class="max-w-2xl space-y-4">@csrf
    <x-ui.input name="title" :label="__('ui.col_name')" :value="old('title')" required />
    <x-ui.select name="academic_year_id" :label="__('ui.nav.years')">
        <option value="">—</option>
        @foreach ($years as $y)
            <option value="{{ $y->id }}" @selected((int) old('academic_year_id') === $y->id)>{{ $y->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="school_class_id" :label="__('ui.nav.classes')" required>
        <option value="">—</option>
        @foreach ($classes as $c)
            <option value="{{ $c->id }}" @selected((int) old('school_class_id') === $c->id)>{{ $c->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="section_id" :label="__('ui.nav.sections')">
        <option value="">—</option>
        @foreach ($sections as $sec)
            <option value="{{ $sec->id }}" @selected((int) old('section_id') === $sec->id)>{{ $sec->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="subject_id" :label="__('ui.nav.subjects')" required>
        <option value="">—</option>
        @foreach ($subjects as $sub)
            <option value="{{ $sub->id }}" @selected((int) old('subject_id') === $sub->id)>{{ $sub->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="staff_id" :label="__('ui.nav.teachers')">
        <option value="">—</option>
        @foreach ($teachers as $t)
            <option value="{{ $t->id }}" @selected((int) old('staff_id') === $t->id)>{{ $t->full_name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="syllabus_topic_id" :label="__('ui.topic')">
        <option value="">—</option>
        @foreach ($topics as $topic)
            <option value="{{ $topic->id }}" @selected((int) old('syllabus_topic_id') === $topic->id)>{{ $topic->title }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.input name="planned_on" type="date" :label="__('ui.planned_on')" :value="old('planned_on')" />
    <x-ui.select name="status" :label="__('ui.col_status')" required>
        <option value="draft" @selected(old('status', 'draft') === 'draft')>{{ __('ui.status_draft') }}</option>
        <option value="published" @selected(old('status') === 'published')>{{ __('ui.status_published') }}</option>
    </x-ui.select>
    <label class="block text-sm font-medium">{{ __('ui.objectives') }}</label>
    <textarea name="objectives" rows="2" class="block w-full rounded-xl border border-sme bg-surface/90 px-3 py-2.5 text-sm">{{ old('objectives') }}</textarea>
    <label class="block text-sm font-medium">{{ __('ui.activities') }}</label>
    <textarea name="activities" rows="2" class="block w-full rounded-xl border border-sme bg-surface/90 px-3 py-2.5 text-sm">{{ old('activities') }}</textarea>
    <label class="block text-sm font-medium">{{ __('ui.resources') }}</label>
    <textarea name="resources" rows="2" class="block w-full rounded-xl border border-sme bg-surface/90 px-3 py-2.5 text-sm">{{ old('resources') }}</textarea>
    <label class="block text-sm font-medium">{{ __('ui.homework_notes') }}</label>
    <textarea name="homework_notes" rows="2" class="block w-full rounded-xl border border-sme bg-surface/90 px-3 py-2.5 text-sm">{{ old('homework_notes') }}</textarea>
    <div class="flex gap-3"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        <a href="{{ route('tenant.lesson-plans.index') }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a></div>
</form>
@endsection
