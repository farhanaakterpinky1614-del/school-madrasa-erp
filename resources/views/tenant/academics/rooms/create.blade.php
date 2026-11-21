@extends('layouts.app')
@section('title', __('ui.nav.rooms'))
@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 07</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.new_record') }}</h1>
    </div>
@endsection
@section('content')
<form method="POST" action="{{ route('tenant.rooms.store') }}" class="max-w-2xl space-y-4">@csrf
    <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name')" required />
    <x-ui.input name="code" :label="__('ui.code')" :value="old('code')" />
    <x-ui.select name="room_type" :label="__('ui.room_type')" required>
        @foreach (['classroom','lab','hall','library','other'] as $t)
            <option value="{{ $t }}" @selected(old('room_type', 'classroom') === $t)>{{ __('ui.room_type_'.$t) }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="branch_id" :label="__('ui.nav.branches')">
        <option value="">—</option>
        @foreach ($branches as $b)
            <option value="{{ $b->id }}" @selected((int) old('branch_id') === $b->id)>{{ $b->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.input name="capacity" type="number" :label="__('ui.capacity')" :value="old('capacity')" />
    <x-ui.input name="building" :label="__('ui.building')" :value="old('building')" />
    <x-ui.input name="floor" :label="__('ui.floor')" :value="old('floor')" />
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) /> {{ __('ui.active') }}</label>
    <div class="flex gap-3"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        <a href="{{ route('tenant.rooms.index') }}" class="text-sm font-medium text-muted">{{ __('ui.cancel') }}</a></div>
</form>
@endsection
