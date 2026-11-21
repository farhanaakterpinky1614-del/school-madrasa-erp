@extends('layouts.app')
@section('title', __('ui.promote'))
@section('page-header')<div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.promote') }} — {{ $student->full_name }}</h1></div>@endsection
@section('content')
<form method="POST" action="{{ route('tenant.students.promotions.store', $student) }}" class="max-w-2xl space-y-4">@csrf
<x-ui.form-section :title="__('ui.promote')">
<x-ui.select name="to_year_id" :label="__('ui.to_year')" required>
@foreach($years as $y)<option value="{{ $y->id }}">{{ $y->name }}</option>@endforeach
</x-ui.select>
<x-ui.select name="to_class_id" :label="__('ui.to_class')" required class="mt-4">
@foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
</x-ui.select>
<x-ui.select name="to_section_id" :label="__('ui.to_section')" class="mt-4">
<option value="">—</option>
@foreach($sections as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</x-ui.select>
<x-ui.input name="roll_number" :label="__('ui.roll_number')" class="mt-4" />
<x-ui.input name="promoted_on" type="date" :label="__('ui.date')" :value="now()->toDateString()" class="mt-4" />
<div class="mt-6"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button></div>
</x-ui.form-section>
</form>
@endsection
