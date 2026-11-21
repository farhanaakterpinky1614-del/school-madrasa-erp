@extends('layouts.app')
@section('title', __('ui.nav.subject_offerings'))
@section('page-header')
    <div><p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 07</p>
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.new_record') }}</h1></div>
@endsection
@section('content')
<form method="POST" action="{{ route('tenant.subject-offerings.store') }}" class="max-w-2xl space-y-4">@csrf
    <x-ui.select name="curriculum_id" :label="__('ui.nav.curricula')" required>
        <option value="">—</option>
        @foreach ($curricula as $cur)
            <option value="{{ $cur->id }}" @selected((int) old('curriculum_id') === $cur->id)>{{ $cur->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="subject_id" :label="__('ui.nav.subjects')" required>
        <option value="">—</option>
        @foreach ($subjects as $sub)
            <option value="{{ $sub->id }}" @selected((int) old('subject_id') === $sub->id)>{{ $sub->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="school_class_id" :label="__('ui.nav.classes')">
        <option value="">—</option>
        @foreach ($classes as $c)
            <option value="{{ $c->id }}" @selected((int) old('school_class_id') === $c->id)>{{ $c->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.input name="weekly_periods" type="number" :label="__('ui.weekly_periods')" :value="old('weekly_periods')" />
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_compulsory" value="1" @checked(old('is_compulsory', true)) /> {{ __('ui.compulsory') }}</label>
    <div class="flex gap-3"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        <a href="{{ route('tenant.subject-offerings.index') }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a></div>
</form>
@endsection
