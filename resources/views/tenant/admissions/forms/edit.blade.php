@extends('layouts.app')
@section('title', $template->name)
@section('page-header')
<div>
<h1 class="text-2xl font-semibold tracking-tight">{{ $template->name }}</h1>
<p class="mt-1.5 text-sm text-muted">{{ __('ui.admission_forms_help') }}</p>
</div>
@endsection
@section('content')
@if (session('status'))<p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>@endif
<form method="POST" action="{{ route('tenant.admission-forms.update', $template) }}" class="mb-8 max-w-2xl">@csrf @method('PUT')
<x-ui.form-section :title="__('ui.form_settings')">
<x-ui.input name="name" :label="__('ui.col_name')" :value="old('name',$template->name)" required />
<x-ui.input name="code" :label="__('ui.code')" :value="old('code',$template->code)" class="mt-4" />
<x-ui.textarea name="description" :label="__('ui.description')" class="mt-4">{{ old('description',$template->description) }}</x-ui.textarea>
<label class="mt-4 flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1" @checked(old('is_default',$template->is_default))> {{ __('ui.default') }}</label>
<label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$template->is_active))> {{ __('ui.status_active') }}</label>
<div class="mt-6"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button></div>
</x-ui.form-section>
</form>
<div class="grid gap-6 lg:grid-cols-2">
<x-ui.form-section :title="__('ui.fields')">
<ul class="space-y-2 text-sm">
@forelse($template->fields as $field)
<li class="flex items-center justify-between gap-3 border-b border-sme/60 py-2">
<span><span class="font-medium">{{ $field->label }}</span> <span class="text-muted">({{ $field->field_key }} · {{ $field->field_type }})</span></span>
<form method="POST" action="{{ route('tenant.admission-forms.fields.destroy', [$template, $field]) }}">@csrf @method('DELETE')
<button type="submit" class="text-sm font-semibold text-danger-600">{{ __('ui.delete') }}</button>
</form>
</li>
@empty
<li class="text-muted">{{ __('ui.no_records') }}</li>
@endforelse
</ul>
</x-ui.form-section>
<form method="POST" action="{{ route('tenant.admission-forms.fields.store', $template) }}">@csrf
<x-ui.form-section :title="__('ui.add_field')">
<div class="grid gap-3 sm:grid-cols-2">
<x-ui.input name="field_key" :label="__('ui.field_key')" required />
<x-ui.input name="label" :label="__('ui.label')" required />
<x-ui.select name="field_type" :label="__('ui.field_type')">
@foreach(['text','number','date','select','textarea','checkbox','file'] as $t)
<option value="{{ $t }}">{{ $t }}</option>
@endforeach
</x-ui.select>
<x-ui.select name="applies_to" :label="__('ui.applies_to')">
<option value="both">both</option>
<option value="online">online</option>
<option value="offline">offline</option>
</x-ui.select>
</div>
<x-ui.input name="options" :label="__('ui.options_csv')" class="mt-3" />
<label class="mt-3 flex items-center gap-2 text-sm"><input type="checkbox" name="is_required" value="1"> {{ __('ui.required') }}</label>
<div class="mt-4"><x-ui.button type="submit" size="sm">{{ __('ui.add_field') }}</x-ui.button></div>
</x-ui.form-section>
</form>
</div>
@endsection
