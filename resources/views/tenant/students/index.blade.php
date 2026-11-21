@extends('layouts.app')

@section('title', __('ui.nav.students'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 05</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.students') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.students_help') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('tenant.students.import.create') }}"><x-ui.button type="button" variant="outline">{{ __('ui.bulk_import') }}</x-ui.button></a>
        <a href="{{ route('tenant.students.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <x-ui.input name="q" :label="__('ui.search')" :value="request('q')" class="min-w-[14rem]" />
        <x-ui.select name="status" :label="__('ui.col_status')">
            <option value="">{{ __('ui.all') }}</option>
            @foreach (['active','withdrawn','alumni','transferred'] as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>{{ __('ui.student_status_'.$st) }}</option>
            @endforeach
        </x-ui.select>
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button></div>
    </form>
    <x-ui.table :headers="[__('ui.student_id'), __('ui.col_name'), __('ui.class_section'), __('ui.col_status'), __('ui.col_action')]">
        @forelse ($students as $student)
            <tr>
                <td class="px-4 py-3 font-mono text-sm">{{ $student->student_code }}</td>
                <td class="px-4 py-3 font-medium">{{ $student->full_name }}</td>
                <td class="px-4 py-3 text-muted">
                    @if($student->currentEnrollment)
                        {{ $student->currentEnrollment->schoolClass?->name }}
                        @if($student->currentEnrollment->section) / {{ $student->currentEnrollment->section->name }} @endif
                    @else — @endif
                </td>
                <td class="px-4 py-3"><x-ui.badge :tone="$student->status === 'active' ? 'success' : 'warning'">{{ __('ui.student_status_'.$student->status) }}</x-ui.badge></td>
                <td class="px-4 py-3"><a href="{{ route('tenant.students.show', $student) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.view') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $students->links() }}</div>
@endsection
