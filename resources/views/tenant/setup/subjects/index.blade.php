@extends('layouts.app')

@section('title', __('ui.nav.subjects'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 04</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.subjects') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.subjects_help') }}</p>
    </div>
    <a href="{{ route('tenant.subjects.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <x-ui.table :headers="[__('ui.col_name'), __('ui.code'), __('ui.subject_type'), __('ui.col_action')]">
        @forelse ($subjects as $subject)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $subject->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $subject->code ?: '—' }}</td>
                <td class="px-4 py-3">{{ $subject->subject_type }}</td>
                <td class="px-4 py-3"><a href="{{ route('tenant.subjects.edit', $subject) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.edit') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $subjects->links() }}</div>
@endsection
