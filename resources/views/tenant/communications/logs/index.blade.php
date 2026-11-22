@extends('layouts.app')

@section('title', __('ui.nav.delivery_logs'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 14</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.delivery_logs') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.delivery_logs_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.search') }}</label>
            <input name="q" value="{{ $filterQ }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.channel') }}</label>
            <select name="channel" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all') }}</option>
                @foreach ($logChannels as $ch)
                    <option value="{{ $ch }}" @selected($filterChannel === $ch)>{{ __('ui.channel_'.$ch) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.status') }}</label>
            <select name="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all') }}</option>
                @foreach ($logStatuses as $st)
                    <option value="{{ $st }}" @selected($filterStatus === $st)>{{ __('ui.delivery_status_'.$st) }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    <x-ui.table :headers="[__('ui.channel'), __('ui.recipient'), __('ui.template'), __('ui.status'), __('ui.driver'), __('ui.sent_at')]">
        @forelse ($logs as $log)
            <tr>
                <td class="px-4 py-3">{{ __('ui.channel_'.$log->channel) }}</td>
                <td class="px-4 py-3">
                    <p class="font-medium">{{ $log->recipient }}</p>
                    @if ($log->error_message)
                        <p class="mt-0.5 text-xs text-rose-600">{{ $log->error_message }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-muted">{{ $log->template?->code ?: '—' }}</td>
                <td class="px-4 py-3">{{ __('ui.delivery_status_'.$log->status) }}</td>
                <td class="px-4 py-3 font-mono text-xs">{{ $log->driver ?: '—' }}</td>
                <td class="px-4 py-3 text-sm text-muted">{{ $log->sent_at?->timezone('Asia/Dhaka')->format('Y-m-d H:i') ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
