@extends('layouts.app')
@section('title', __('ui.nav.routines'))
@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 07</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.routines') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.routines_help') }}</p>
    </div>
    <a href="{{ route('tenant.routines.create') }}"><x-ui.button type="button">{{ __('ui.new_record') }}</x-ui.button></a>
@endsection
@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <x-ui.select name="class_id" :label="__('ui.nav.classes')">
            <option value="">{{ __('ui.all') }}</option>
            @foreach ($classes as $c)
                <option value="{{ $c->id }}" @selected((int) request('class_id') === $c->id)>{{ $c->name }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select name="day" :label="__('ui.day')">
            <option value="">{{ __('ui.all') }}</option>
            @foreach ($days as $num => $label)
                <option value="{{ $num }}" @selected(request('day') !== null && request('day') !== '' && (int) request('day') === $num)>{{ $label }}</option>
            @endforeach
        </x-ui.select>
        <div class="flex items-end"><x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button></div>
    </form>
    <x-ui.table :headers="[__('ui.day'), __('ui.nav.periods'), __('ui.nav.classes'), __('ui.nav.sections'), __('ui.nav.subjects'), __('ui.nav.teachers'), __('ui.nav.rooms'), __('ui.col_action')]">
        @forelse ($routines as $routine)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $days[$routine->day_of_week] ?? $routine->day_of_week }}</td>
                <td class="px-4 py-3">{{ $routine->period?->name }}</td>
                <td class="px-4 py-3">{{ $routine->schoolClass?->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $routine->section?->name ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $routine->subject?->name ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $routine->staff?->full_name ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $routine->room?->name ?: '—' }}</td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('tenant.routines.destroy', $routine) }}">@csrf @method('DELETE')
                        <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $routines->links() }}</div>
@endsection
