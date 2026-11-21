@extends('layouts.app')
@section('title', __('ui.nav.syllabi'))
@section('page-header')
    <div><p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 07</p>
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.new_record') }}</h1></div>
@endsection
@section('content')
<form method="POST" action="{{ route('tenant.syllabi.store') }}" class="max-w-2xl space-y-4">@csrf
    <x-ui.input name="title" :label="__('ui.col_name')" :value="old('title')" required />
    <x-ui.select name="curriculum_id" :label="__('ui.nav.curricula')">
        <option value="">—</option>
        @foreach ($curricula as $cur)
            <option value="{{ $cur->id }}" @selected((int) old('curriculum_id') === $cur->id)>{{ $cur->name }}</option>
        @endforeach
    </x-ui.select>
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
    <x-ui.select name="subject_id" :label="__('ui.nav.subjects')" required>
        <option value="">—</option>
        @foreach ($subjects as $sub)
            <option value="{{ $sub->id }}" @selected((int) old('subject_id') === $sub->id)>{{ $sub->name }}</option>
        @endforeach
    </x-ui.select>
    <label class="block text-sm font-medium">{{ __('ui.description') }}</label>
    <textarea name="description" rows="3" class="block w-full rounded-xl border border-sme bg-surface/90 px-3 py-2.5 text-sm">{{ old('description') }}</textarea>
    <div class="flex gap-3"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        <a href="{{ route('tenant.syllabi.index') }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a></div>
</form>
@endsection
