@extends('layouts.app')
@section('title', __('ui.nav.subject_offerings'))
@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 07</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.subject_offerings') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.subject_offerings_help') }}</p>
    </div>
    <a href="{{ route('tenant.subject-offerings.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
@endsection
@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <x-ui.table :headers="[__('ui.nav.curricula'), __('ui.nav.subjects'), __('ui.nav.classes'), __('ui.weekly_periods'), __('ui.compulsory'), __('ui.col_action')]">
        @forelse ($offerings as $offering)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $offering->curriculum?->name }}</td>
                <td class="px-4 py-3">{{ $offering->subject?->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $offering->schoolClass?->name ?: '—' }}</td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('tenant.subject-offerings.update', $offering) }}" class="flex items-center gap-2">@csrf @method('PUT')
                        <input type="number" name="weekly_periods" value="{{ $offering->weekly_periods }}" min="1" max="40" class="w-20 rounded-lg border border-sme px-2 py-1 text-sm" />
                        <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="is_compulsory" value="1" @checked($offering->is_compulsory) /> {{ __('ui.yes') }}</label>
                        <button type="submit" class="text-sm font-semibold text-primary-600">{{ __('ui.save') }}</button>
                    </form>
                </td>
                <td class="px-4 py-3 text-muted">{{ $offering->is_compulsory ? __('ui.yes') : __('ui.no') }}</td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('tenant.subject-offerings.destroy', $offering) }}">@csrf @method('DELETE')
                        <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $offerings->links() }}</div>
@endsection
