@extends('layouts.app')
@section('title', __('ui.nav.admission_forms'))
@section('page-header')<div><h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.new_form_template') }}</h1></div>@endsection
@section('content')
<form method="POST" action="{{ route('tenant.admission-forms.store') }}" class="max-w-2xl">@csrf
<x-ui.form-section :title="__('ui.nav.admission_forms')">
<x-ui.input name="name" :label="__('ui.col_name')" required />
<x-ui.input name="code" :label="__('ui.code')" class="mt-4" />
<x-ui.textarea name="description" :label="__('ui.description')" class="mt-4"></x-ui.textarea>
<label class="mt-4 flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1"> {{ __('ui.default') }}</label>
<label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> {{ __('ui.status_active') }}</label>
<div class="mt-6"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button></div>
</x-ui.form-section>
</form>
@endsection
