@extends('layouts.app')
@section('title', __('ui.nav.guardians'))
@section('page-header')<div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.new_guardian') }}</h1></div>@endsection
@section('content')
<form method="POST" action="{{ route('tenant.guardians.store') }}" class="max-w-2xl space-y-4">@csrf
<x-ui.form-section :title="__('ui.nav.guardians')">
<div class="grid gap-4 sm:grid-cols-2">
<x-ui.input name="name" :label="__('ui.col_name')" :value="old('name')" required />
<x-ui.input name="phone" :label="__('ui.phone')" :value="old('phone')" />
<x-ui.input name="email" type="email" :label="__('ui.field_email')" :value="old('email')" />
<x-ui.input name="occupation" :label="__('ui.occupation')" :value="old('occupation')" />
</div>
<x-ui.textarea name="address" :label="__('ui.address')" class="mt-4">{{ old('address') }}</x-ui.textarea>
<x-ui.select name="student_id" :label="__('ui.link_to_student')" class="mt-4">
<option value="">—</option>
@foreach($students as $s)
<option value="{{ $s->id }}" @selected(old('student_id', $preselect)==$s->id)>{{ $s->full_name }} ({{ $s->student_code }})</option>
@endforeach
</x-ui.select>
<x-ui.input name="relationship" :label="__('ui.relationship')" :value="old('relationship','father')" class="mt-4" />
<div class="mt-6 flex gap-3"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button></div>
</x-ui.form-section>
</form>
@endsection
