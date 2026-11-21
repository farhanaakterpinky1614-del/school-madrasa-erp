@extends('layouts.app')
@section('title', __('ui.nav.routines'))
@section('page-header')
    <div><p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 07</p>
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.new_record') }}</h1></div>
@endsection
@section('content')
<form method="POST" action="{{ route('tenant.routines.store') }}" class="max-w-2xl space-y-4">@csrf
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
    <x-ui.select name="day_of_week" :label="__('ui.day')" required>
        @foreach ($days as $num => $label)
            <option value="{{ $num }}" @selected((int) old('day_of_week', 0) === $num)>{{ $label }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="period_id" :label="__('ui.nav.periods')" required>
        <option value="">—</option>
        @foreach ($periods as $p)
            <option value="{{ $p->id }}" @selected((int) old('period_id') === $p->id)>{{ $p->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="subject_id" :label="__('ui.nav.subjects')">
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
    <x-ui.select name="room_id" :label="__('ui.nav.rooms')">
        <option value="">—</option>
        @foreach ($rooms as $r)
            <option value="{{ $r->id }}" @selected((int) old('room_id') === $r->id)>{{ $r->name }}</option>
        @endforeach
    </x-ui.select>
    <div class="flex gap-3"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        <a href="{{ route('tenant.routines.index') }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a></div>
</form>
@endsection
