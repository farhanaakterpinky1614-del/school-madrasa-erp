@extends('layouts.app')

@section('title', __('ui.nav.sessions'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 03</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.sessions') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.sessions_help') }}</p>
    </div>
    <form method="POST" action="{{ route('tenant.sessions.destroy-others') }}">
        @csrf
        @method('DELETE')
        <x-ui.button type="submit">{{ __('ui.revoke_other_sessions') }}</x-ui.button>
    </form>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</p>
    @endif

    <x-ui.table :headers="[__('ui.session_id'), __('ui.ip'), __('ui.last_activity'), __('ui.col_action')]">
        @forelse ($sessions as $session)
            <tr>
                <td class="px-4 py-3 font-mono text-xs">
                    {{ \Illuminate\Support\Str::limit($session->id, 12) }}
                    @if ($session->id === $currentId)
                        <x-ui.badge tone="success">{{ __('ui.current') }}</x-ui.badge>
                    @endif
                </td>
                <td class="px-4 py-3 font-mono text-xs">{{ $session->ip_address }}</td>
                <td class="px-4 py-3 text-muted">{{ \Illuminate\Support\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</td>
                <td class="px-4 py-3">
                    @if ($session->id !== $currentId)
                        <form method="POST" action="{{ route('tenant.sessions.destroy', $session->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.revoke') }}</button>
                        </form>
                    @else
                        —
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-muted">
                    {{ __('ui.sessions_driver_note') }}
                </td>
            </tr>
        @endforelse
    </x-ui.table>
@endsection
