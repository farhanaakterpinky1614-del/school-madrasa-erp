@extends('layouts.app')
@section('title', $application->application_number)
@section('page-header')
<div>
<p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">{{ $application->application_number }}</p>
<h1 class="text-2xl font-semibold tracking-tight">{{ $application->full_name }}</h1>
<p class="mt-1.5 text-sm text-muted">{{ __('ui.app_status_'.$application->status) }} · {{ $application->channel }}</p>
</div>
@endsection
@section('content')
@if (session('status'))<p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>@endif
<div class="grid gap-6 lg:grid-cols-2">
<x-ui.form-section :title="__('ui.application_details')">
<dl class="grid grid-cols-2 gap-3 text-sm">
<div><dt class="text-muted">{{ __('ui.phone') }}</dt><dd>{{ $application->phone ?: '—' }}</dd></div>
<div><dt class="text-muted">{{ __('ui.date_of_birth') }}</dt><dd>{{ optional($application->date_of_birth)->format('Y-m-d') ?: '—' }}</dd></div>
<div><dt class="text-muted">{{ __('ui.guardian') }}</dt><dd>{{ $application->guardian_name }} ({{ $application->guardian_phone }})</dd></div>
<div><dt class="text-muted">{{ __('ui.nav.classes') }}</dt><dd>{{ $application->schoolClass?->name ?: '—' }}</dd></div>
</dl>
@if($application->form_payload)
<pre class="mt-4 overflow-auto rounded-xl bg-slate-50 p-3 text-xs">{{ json_encode($application->form_payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
@endif
@if($application->student)
<p class="mt-4 text-sm">{{ __('ui.linked_student') }}: <a class="font-semibold text-primary-600" href="{{ route('tenant.students.show', $application->student) }}">{{ $application->student->student_code }}</a></p>
@endif
</x-ui.form-section>
<x-ui.form-section :title="__('ui.workflow')">
<div class="flex flex-wrap gap-2">
@if(in_array($application->status, ['submitted','under_review'], true))
<form method="POST" action="{{ route('tenant.applications.review', $application) }}">@csrf<x-ui.button type="submit" size="sm" variant="secondary">{{ __('ui.mark_under_review') }}</x-ui.button></form>
<form method="POST" action="{{ route('tenant.applications.approve', $application) }}">@csrf
<input type="hidden" name="decision_notes" value="Approved" />
<x-ui.button type="submit" size="sm" variant="success">{{ __('ui.approve') }}</x-ui.button>
</form>
<form method="POST" action="{{ route('tenant.applications.reject', $application) }}">@csrf
<input type="hidden" name="decision_notes" value="Rejected" />
<x-ui.button type="submit" size="sm" variant="danger">{{ __('ui.reject') }}</x-ui.button>
</form>
@endif
</div>
@if($application->status === 'approved')
<form method="POST" action="{{ route('tenant.applications.enroll', $application) }}" class="mt-6 space-y-3">@csrf
<x-ui.select name="academic_year_id" :label="__('ui.nav.years')" required>
@foreach($years as $y)<option value="{{ $y->id }}" @selected($y->is_current)>{{ $y->name }}</option>@endforeach
</x-ui.select>
<x-ui.select name="school_class_id" :label="__('ui.nav.classes')" required>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected($application->school_class_id==$c->id)>{{ $c->name }}</option>@endforeach
</x-ui.select>
<x-ui.select name="section_id" :label="__('ui.nav.sections')">
<option value="">—</option>
@foreach($sections as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</x-ui.select>
<x-ui.input name="roll_number" :label="__('ui.roll_number')" />
<x-ui.button type="submit">{{ __('ui.convert_enroll') }}</x-ui.button>
</form>
@endif
</x-ui.form-section>
</div>
@endsection
