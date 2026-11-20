@extends('layouts.app')

@section('title', __('ui.nav.calendar'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 04</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.calendar') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.calendar_help') }}</p>
    </div>
    <a href="{{ route('tenant.calendar.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <x-ui.form-section :title="__('ui.working_days')" class="mb-6">
        <p class="text-sm text-muted">{{ __('ui.working_days_hint') }}:
            <span class="font-semibold text-[color:var(--sme-text)]">{{ implode(', ', $workingDays ?: []) ?: '—' }}</span>
        </p>
        <p class="mt-2 text-sm"><a href="{{ route('tenant.settings.edit') }}" class="font-semibold text-primary-600">{{ __('ui.edit_settings') }}</a></p>
    </x-ui.form-section>

    <x-ui.table :headers="[__('ui.col_name'), __('ui.event_type'), __('ui.starts_on'), __('ui.ends_on'), __('ui.col_action')]">
        @forelse ($events as $event)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $event->title }}</td>
                <td class="px-4 py-3">{{ $event->event_type }}</td>
                <td class="px-4 py-3 text-muted">{{ optional($event->starts_on)->format('Y-m-d') }}</td>
                <td class="px-4 py-3 text-muted">{{ optional($event->ends_on)->format('Y-m-d') ?: '—' }}</td>
                <td class="px-4 py-3"><a href="{{ route('tenant.calendar.edit', $event) }}" class="text-sm font-semibold text-primary-600">{{ __('ui.edit') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $events->links() }}</div>
@endsection
