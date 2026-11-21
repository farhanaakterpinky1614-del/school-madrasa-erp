@extends('layouts.app')

@section('title', __('ui.nav.assignments'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.assignments') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.assignments_help') }}</p>
    </div>
    <a href="{{ route('tenant.assignments.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <x-ui.table :headers="[__('ui.nav.teachers'), __('ui.nav.classes'), __('ui.nav.sections'), __('ui.nav.subjects'), __('ui.nav.years'), __('ui.col_action')]">
        @forelse ($assignments as $assignment)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $assignment->staff?->full_name }}</td>
                <td class="px-4 py-3">{{ $assignment->schoolClass?->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $assignment->section?->name ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $assignment->subject?->name ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $assignment->academicYear?->name ?: '—' }}</td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('tenant.assignments.destroy', $assignment) }}">@csrf @method('DELETE')
                        <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $assignments->links() }}</div>
@endsection
