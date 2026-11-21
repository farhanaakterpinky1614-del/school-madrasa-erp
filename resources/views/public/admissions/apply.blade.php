@extends('layouts.guest')
@section('title', __('ui.online_admission'))
@section('content')
<div class="mx-auto max-w-2xl px-4 py-10">
<div class="mb-8 text-center">
<p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-600">{{ $tenant->name }}</p>
<h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ $session->name }}</h1>
<p class="mt-2 text-sm text-muted">{{ __('ui.online_admission_help') }}</p>
</div>
@if ($errors->any())
<div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
<ul class="list-disc ps-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
<form method="POST" action="{{ route('public.admissions.store', [$tenant->slug, $session->public_slug]) }}" class="space-y-4 rounded-2xl border border-sme bg-white/80 p-6 shadow-sme-soft">@csrf
<div class="grid gap-4 sm:grid-cols-2">
<x-ui.input name="first_name" :label="__('ui.first_name')" :value="old('first_name')" required />
<x-ui.input name="last_name" :label="__('ui.last_name')" :value="old('last_name')" />
<x-ui.select name="gender" :label="__('ui.gender')">
<option value="">—</option>
@foreach(['male','female','other'] as $g)<option value="{{ $g }}" @selected(old('gender')===$g)>{{ __('ui.gender_'.$g) }}</option>@endforeach
</x-ui.select>
<x-ui.input name="date_of_birth" type="date" :label="__('ui.date_of_birth')" :value="old('date_of_birth')" />
<x-ui.input name="phone" :label="__('ui.phone')" :value="old('phone')" />
<x-ui.input name="email" type="email" :label="__('ui.field_email')" :value="old('email')" />
<x-ui.input name="guardian_name" :label="__('ui.guardian')" :value="old('guardian_name')" required />
<x-ui.input name="guardian_phone" :label="__('ui.guardian_phone')" :value="old('guardian_phone')" required />
</div>
<x-ui.textarea name="address" :label="__('ui.address')">{{ old('address') }}</x-ui.textarea>
@foreach($fields as $field)
@if($field->field_type === 'textarea')
<x-ui.textarea :name="'custom['.$field->field_key.']'" :label="$field->label" :required="$field->is_required">{{ old('custom.'.$field->field_key) }}</x-ui.textarea>
@elseif($field->field_type === 'select')
<x-ui.select :name="'custom['.$field->field_key.']'" :label="$field->label" :required="$field->is_required">
<option value="">—</option>
@foreach(($field->options ?? []) as $opt)
<option value="{{ $opt }}" @selected(old('custom.'.$field->field_key)===$opt)>{{ $opt }}</option>
@endforeach
</x-ui.select>
@else
<x-ui.input :name="'custom['.$field->field_key.']'" :type="$field->field_type === 'number' ? 'number' : ($field->field_type === 'date' ? 'date' : 'text')" :label="$field->label" :value="old('custom.'.$field->field_key)" :required="$field->is_required" />
@endif
@endforeach
<x-ui.button type="submit" class="w-full">{{ __('ui.submit_application') }}</x-ui.button>
</form>
</div>
@endsection
