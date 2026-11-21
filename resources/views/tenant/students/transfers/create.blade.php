@extends('layouts.app')
@section('title', __('ui.transfer'))
@section('page-header')<div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.transfer') }} — {{ $student->full_name }}</h1></div>@endsection
@section('content')
<form method="POST" action="{{ route('tenant.students.transfers.store', $student) }}" class="max-w-2xl space-y-4">@csrf
<x-ui.form-section :title="__('ui.transfer')">
@if($student->currentEnrollment)
<p class="mb-4 text-sm text-muted">{{ __('ui.current_enrollment') }}: {{ $student->currentEnrollment->schoolClass?->name }} @if($student->currentEnrollment->section)/ {{ $student->currentEnrollment->section->name }}@endif</p>
@endif
<x-ui.select name="academic_year_id" :label="__('ui.nav.years')" required>
@foreach($years as $y)<option value="{{ $y->id }}" @selected($y->is_current)>{{ $y->name }}</option>@endforeach
</x-ui.select>
<x-ui.select name="to_class_id" :label="__('ui.to_class')" required class="mt-4">
@foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
</x-ui.select>
<x-ui.select name="to_section_id" :label="__('ui.to_section')" class="mt-4">
<option value="">—</option>
@foreach($sections as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</x-ui.select>
<x-ui.input name="roll_number" :label="__('ui.roll_number')" class="mt-4" />
<x-ui.input name="transferred_on" type="date" :label="__('ui.date')" :value="now()->toDateString()" class="mt-4" />
<x-ui.input name="reason" :label="__('ui.reason')" class="mt-4" />
<div class="mt-6"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button></div>
</x-ui.form-section>
</form>
@endsection
