@extends('layouts.app')

@section('title', __('ui.nav.designations'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $designation->name }}</h1>
    </div>
@endsection

@section('content')
<form method="POST" action="{{ route('tenant.designations.update', $designation) }}" class="max-w-xl space-y-4">@csrf @method('PUT')
    <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name', $designation->name)" required />
    <x-ui.input name="code" :label="__('ui.code')" :value="old('code', $designation->code)" />
    <x-ui.textarea name="description" :label="__('ui.description')" :value="old('description', $designation->description)" />
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $designation->is_active)) /> {{ __('ui.staff_status_active') }}</label>
    <div class="flex gap-3"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        <a href="{{ route('tenant.designations.index') }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a></div>
</form>
@endsection
