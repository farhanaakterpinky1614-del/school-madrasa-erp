@extends('layouts.app')
@section('title', __('ui.nav.guardians'))
@section('page-header')<div><h1 class="text-2xl font-semibold tracking-tight">{{ $guardian->name }}</h1></div>@endsection
@section('content')
<form method="POST" action="{{ route('tenant.guardians.update', $guardian) }}" class="max-w-2xl space-y-4">@csrf @method('PUT')
<x-ui.form-section :title="__('ui.nav.guardians')">
<div class="grid gap-4 sm:grid-cols-2">
<x-ui.input name="name" :label="__('ui.col_name')" :value="old('name',$guardian->name)" required />
<x-ui.input name="phone" :label="__('ui.phone')" :value="old('phone',$guardian->phone)" />
<x-ui.input name="email" type="email" :label="__('ui.field_email')" :value="old('email',$guardian->email)" />
<x-ui.input name="occupation" :label="__('ui.occupation')" :value="old('occupation',$guardian->occupation)" />
</div>
<x-ui.textarea name="address" :label="__('ui.address')" class="mt-4">{{ old('address',$guardian->address) }}</x-ui.textarea>
<div class="mt-6"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button></div>
</x-ui.form-section>
</form>
@endsection
