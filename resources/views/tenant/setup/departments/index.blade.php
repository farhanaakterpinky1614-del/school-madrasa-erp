@extends('layouts.app')

@section('title', __('ui.nav.departments'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 04</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.departments') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.departments_help') }}</p>
    </div>
    <a href="{{ route('tenant.departments.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <x-ui.table :headers="[__('ui.col_name'), __('ui.code'), __('ui.nav.branches'), __('ui.kind'), __('ui.col_action')]">
        @forelse ($departments as $department)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $department->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $department->code ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $department->branch?->name ?: '—' }}</td>
                <td class="px-4 py-3">{{ $department->kind }}</td>
                <td class="px-4 py-3"><a href="{{ route('tenant.departments.edit', $department) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.edit') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $departments->links() }}</div>
@endsection
