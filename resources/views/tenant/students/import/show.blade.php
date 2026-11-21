@extends('layouts.app')
@section('title', __('ui.bulk_import'))
@section('page-header')
<div>
<h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.import_preview') }}</h1>
<p class="mt-1.5 text-sm text-muted">{{ $batch->filename }} · {{ $batch->valid_rows }}/{{ $batch->total_rows }} {{ __('ui.valid') }}</p>
</div>
@endsection
@section('content')
@if (session('status'))<p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>@endif
<div class="mb-6 grid gap-4 sm:grid-cols-3">
<div class="rounded-xl border border-sme p-4"><p class="text-xs text-muted">{{ __('ui.total') }}</p><p class="text-2xl font-semibold">{{ $batch->total_rows }}</p></div>
<div class="rounded-xl border border-sme p-4"><p class="text-xs text-muted">{{ __('ui.valid') }}</p><p class="text-2xl font-semibold text-emerald-700">{{ $batch->valid_rows }}</p></div>
<div class="rounded-xl border border-sme p-4"><p class="text-xs text-muted">{{ __('ui.invalid') }}</p><p class="text-2xl font-semibold text-rose-700">{{ $batch->invalid_rows }}</p></div>
</div>
@if($batch->preview_errors)
<x-ui.form-section :title="__('ui.validation_errors')" class="mb-6">
<ul class="space-y-1 text-sm text-rose-700">
@foreach($batch->preview_errors as $err)
<li>Line {{ $err['line'] }}: {{ implode('; ', $err['errors']) }}</li>
@endforeach
</ul>
</x-ui.form-section>
@endif
@if($batch->status !== 'imported' && $batch->valid_rows > 0)
<form method="POST" action="{{ route('tenant.students.import.commit', $batch) }}" class="max-w-2xl space-y-4">@csrf
<x-ui.form-section :title="__('ui.commit_import')">
<p class="mb-3 text-sm text-muted">{{ __('ui.commit_import_help') }}</p>
<x-ui.select name="academic_year_id" :label="__('ui.nav.years')">
<option value="">—</option>
@foreach($years as $y)<option value="{{ $y->id }}" @selected($y->is_current)>{{ $y->name }}</option>@endforeach
</x-ui.select>
<x-ui.select name="school_class_id" :label="__('ui.nav.classes')" class="mt-4">
<option value="">—</option>
@foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
</x-ui.select>
<x-ui.select name="section_id" :label="__('ui.nav.sections')" class="mt-4">
<option value="">—</option>
@foreach($sections as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</x-ui.select>
<div class="mt-6"><x-ui.button type="submit">{{ __('ui.import_valid_rows') }}</x-ui.button></div>
</x-ui.form-section>
</form>
@endif
@endsection
