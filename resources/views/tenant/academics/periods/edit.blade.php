@extends('layouts.app')
@section('title', __('ui.nav.periods'))
@section('page-header')
    <div><p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 07</p>
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.edit') }}</h1></div>
@endsection
@section('content')
<form method="POST" action="{{ route('tenant.periods.update', $period) }}" class="max-w-2xl space-y-4">@csrf @method('PUT')
    <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name', $period->name)" required />
    <x-ui.input name="code" :label="__('ui.code')" :value="old('code', $period->code)" />
    <x-ui.input name="sort_order" type="number" :label="__('ui.sort_order')" :value="old('sort_order', $period->sort_order)" />
    <x-ui.select name="shift_id" :label="__('ui.nav.shifts')">
        <option value="">—</option>
        @foreach ($shifts as $s)
            <option value="{{ $s->id }}" @selected((int) old('shift_id', $period->shift_id) === $s->id)>{{ $s->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.input name="starts_at" type="time" :label="__('ui.starts_at')" :value="old('starts_at', $period->starts_at ? \Illuminate\Support\Str::of($period->starts_at)->substr(0,5) : '')" />
    <x-ui.input name="ends_at" type="time" :label="__('ui.ends_at')" :value="old('ends_at', $period->ends_at ? \Illuminate\Support\Str::of($period->ends_at)->substr(0,5) : '')" />
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_break" value="1" @checked(old('is_break', $period->is_break)) /> {{ __('ui.break') }}</label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $period->is_active)) /> {{ __('ui.active') }}</label>
    <div class="flex gap-3"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        <a href="{{ route('tenant.periods.index') }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a></div>
</form>
@endsection
