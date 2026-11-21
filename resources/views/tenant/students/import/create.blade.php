@extends('layouts.app')
@section('title', __('ui.bulk_import'))
@section('page-header')
<div>
<p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 05</p>
<h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.bulk_import') }}</h1>
<p class="mt-1.5 text-sm text-muted">{{ __('ui.bulk_import_help') }}</p>
</div>
@endsection
@section('content')
<form method="POST" action="{{ route('tenant.students.import.preview') }}" enctype="multipart/form-data" class="max-w-xl space-y-4">@csrf
<x-ui.form-section :title="__('ui.upload_csv_excel')">
<p class="mb-3 text-sm text-muted">{{ __('ui.bulk_import_columns') }}</p>
<input type="file" name="file" required accept=".csv,.xlsx,.xls,.txt" class="block w-full text-sm" />
<div class="mt-6"><x-ui.button type="submit">{{ __('ui.preview_validate') }}</x-ui.button></div>
</x-ui.form-section>
</form>
@endsection
