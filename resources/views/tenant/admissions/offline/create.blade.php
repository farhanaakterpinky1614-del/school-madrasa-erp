@extends('layouts.app')
@section('title', __('ui.offline_admission'))
@section('page-header')
<div>
<p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 05</p>
<h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.offline_admission') }}</h1>
</div>
@endsection
@section('content')
<form method="POST" action="{{ route('tenant.applications.offline.store') }}" class="max-w-2xl">@csrf
<x-ui.form-section :title="__('ui.offline_admission')">
<x-ui.select name="admission_session_id" :label="__('ui.nav.admission_sessions')" required>
@foreach($sessions as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</x-ui.select>
<div class="mt-4 grid gap-4 sm:grid-cols-2">
<x-ui.input name="first_name" :label="__('ui.first_name')" required />
<x-ui.input name="last_name" :label="__('ui.last_name')" />
<x-ui.select name="gender" :label="__('ui.gender')">
<option value="">—</option>
@foreach(['male','female','other'] as $g)<option value="{{ $g }}">{{ __('ui.gender_'.$g) }}</option>@endforeach
</x-ui.select>
<x-ui.input name="date_of_birth" type="date" :label="__('ui.date_of_birth')" />
<x-ui.input name="phone" :label="__('ui.phone')" />
<x-ui.input name="guardian_name" :label="__('ui.guardian')" />
<x-ui.input name="guardian_phone" :label="__('ui.guardian_phone')" />
<x-ui.input name="guardian_relation" :label="__('ui.relationship')" value="father" />
<x-ui.select name="school_class_id" :label="__('ui.nav.classes')">
<option value="">—</option>
@foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
</x-ui.select>
</div>
<div class="mt-6"><x-ui.button type="submit">{{ __('ui.submit_application') }}</x-ui.button></div>
</x-ui.form-section>
</form>
@endsection
