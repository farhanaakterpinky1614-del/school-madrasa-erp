@extends('layouts.app')
@section('title', __('ui.id_card'))
@section('page-header')
<div>
<h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.id_card') }}</h1>
<p class="mt-1.5 text-sm text-muted">{{ __('ui.id_card_help') }}</p>
</div>
<button type="button" onclick="window.print()" class="print:hidden"><x-ui.button type="button">{{ __('ui.print') }}</x-ui.button></button>
@endsection
@section('content')
<div class="mx-auto max-w-md overflow-hidden rounded-2xl border border-sme bg-gradient-to-br from-slate-50 via-white to-indigo-50 shadow-sme-soft print:shadow-none">
<div class="bg-gradient-to-r from-indigo-600 to-slate-800 px-5 py-4 text-white">
<p class="text-xs uppercase tracking-[0.16em] text-indigo-100">{{ $institution?->name ?? config('app.name') }}</p>
<p class="mt-1 text-lg font-semibold">{{ __('ui.student_id_card') }}</p>
</div>
<div class="flex gap-4 p-5">
<div class="flex-1 space-y-2 text-sm">
<p class="text-xl font-semibold tracking-tight">{{ $student->full_name }}</p>
<p class="font-mono text-primary-700">{{ $student->student_code }}</p>
<p class="text-muted">{{ $student->currentEnrollment?->schoolClass?->name }}
@if($student->currentEnrollment?->section) · {{ $student->currentEnrollment->section->name }}@endif</p>
<p class="text-muted">{{ __('ui.admission_number') }}: {{ $student->admission_number ?: '—' }}</p>
</div>
<img src="{{ $qrDataUri }}" alt="QR" class="h-28 w-28 rounded-xl border border-sme bg-white p-1" />
</div>
<p class="border-t border-sme px-5 py-3 text-xs text-muted">{{ $student->qr_payload }}</p>
</div>
@endsection
