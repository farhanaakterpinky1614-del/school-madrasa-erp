@extends('layouts.app')

@section('title', __('ui.nav.teachers'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 06</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.teachers') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.teachers_help') }}</p>
    </div>
    <a href="{{ route('tenant.assignments.create') }}"><x-ui.button type="button" variant="outline">{{ __('ui.nav.assignments') }}</x-ui.button></a>
@endsection

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <x-ui.input name="q" :label="__('ui.search')" :value="request('q')" class="min-w-[14rem]" />
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button></div>
    </form>
    <x-ui.table :headers="[__('ui.employee_code'), __('ui.col_name'), __('ui.designation'), __('ui.nav.assignments'), __('ui.col_action')]">
        @forelse ($teachers as $teacher)
            <tr>
                <td class="px-4 py-3 font-mono text-sm">{{ $teacher->employee_code }}</td>
                <td class="px-4 py-3 font-medium">{{ $teacher->full_name }}</td>
                <td class="px-4 py-3 text-muted">{{ $teacher->designation?->name ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $teacher->assignments->count() }}</td>
                <td class="px-4 py-3"><a href="{{ route('tenant.staff.show', $teacher) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.view') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $teachers->links() }}</div>
@endsection
