@extends('layouts.app')
@section('title', __('ui.nav.teacher_routines'))
@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 07</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.teacher_routines') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.teacher_routines_help') }}</p>
    </div>
@endsection
@section('content')
    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <x-ui.select name="staff_id" :label="__('ui.nav.teachers')">
            <option value="">{{ __('ui.all') }}</option>
            @foreach ($teachers as $t)
                <option value="{{ $t->id }}" @selected((int) $staffId === $t->id)>{{ $t->full_name }}</option>
            @endforeach
        </x-ui.select>
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button></div>
    </form>
    <x-ui.table :headers="[__('ui.nav.teachers'), __('ui.day'), __('ui.nav.periods'), __('ui.nav.classes'), __('ui.nav.subjects'), __('ui.nav.rooms')]">
        @forelse ($routines as $routine)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $routine->staff?->full_name }}</td>
                <td class="px-4 py-3">{{ $days[$routine->day_of_week] ?? $routine->day_of_week }}</td>
                <td class="px-4 py-3">{{ $routine->period?->name }}</td>
                <td class="px-4 py-3">{{ $routine->schoolClass?->name }} {{ $routine->section?->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $routine->subject?->name ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $routine->room?->name ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $routines->links() }}</div>
@endsection
