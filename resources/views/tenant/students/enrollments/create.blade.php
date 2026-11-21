@extends('layouts.app')
@section('title', __('ui.enroll'))
@section('page-header')<div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.enroll') }} — {{ $student->full_name }}</h1></div>@endsection
@section('content')
<form method="POST" action="{{ route('tenant.students.enrollments.store', $student) }}" class="max-w-2xl space-y-4">@csrf
<x-ui.form-section :title="__('ui.enrollment')">
<x-ui.select name="academic_year_id" :label="__('ui.nav.years')" required>
@foreach($years as $y)<option value="{{ $y->id }}" @selected($y->is_current)>{{ $y->name }}</option>@endforeach
</x-ui.select>
<x-ui.select name="school_class_id" :label="__('ui.nav.classes')" required class="mt-4">
<option value="">—</option>
@foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
</x-ui.select>
<x-ui.select name="section_id" :label="__('ui.nav.sections')" class="mt-4">
<option value="">—</option>
@foreach($sections as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</x-ui.select>
<x-ui.select name="shift_id" :label="__('ui.nav.shifts')" class="mt-4">
<option value="">—</option>
@foreach($shifts as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</x-ui.select>
<x-ui.input name="roll_number" :label="__('ui.roll_number')" class="mt-4" />
<x-ui.input name="enrolled_on" type="date" :label="__('ui.enrolled_on')" :value="now()->toDateString()" class="mt-4" />
<div class="mt-6"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button></div>
</x-ui.form-section>
</form>
@endsection
