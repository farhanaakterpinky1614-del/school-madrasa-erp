@extends('layouts.app')

@section('title', __('ui.edit_exam'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 09</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.edit_exam') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ $exam->name }}</p>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.exams.update', $exam) }}" class="max-w-3xl space-y-4">
        @csrf
        @method('PUT')
        @include('tenant.examinations.exams._form')
        <div class="flex gap-3">
            <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
            <a href="{{ route('tenant.exams.show', $exam) }}" class="inline-flex items-center text-sm font-medium text-muted">{{ __('ui.cancel') }}</a>
        </div>
    </form>
@endsection
