@extends('layouts.app')

@section('title', __('ui.nav.hostel_rooms'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 13</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.hostel_rooms') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.hostel_rooms_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
            <ul class="list-disc ps-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.hostel') }}</label>
            <select name="hostel_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all') }}</option>
                @foreach ($hostels as $hostel)
                    <option value="{{ $hostel->id }}" @selected($filterHostelId === $hostel->id)>{{ $hostel->name }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    @can('hostel.room.manage')
        <form method="POST" action="{{ route('tenant.hostel.rooms.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-3">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.hostel') }}</label>
                <select name="hostel_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    <option value="">{{ __('ui.select') }}</option>
                    @foreach ($hostels as $hostel)
                        <option value="{{ $hostel->id }}">{{ $hostel->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.room_number') }}</label>
                <input name="room_number" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('room_number') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.floor') }}</label>
                <input name="floor" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('floor') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.bed_count') }}</label>
                <input type="number" name="bed_count" min="1" max="20" value="{{ old('bed_count', 2) }}" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.bed_labels') }}</label>
                <input name="bed_labels" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" placeholder="{{ __('ui.bed_labels_placeholder') }}" value="{{ old('bed_labels') }}">
            </div>
            <div class="flex items-end">
                <x-ui.button type="submit">{{ __('ui.add_hostel_room') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <x-ui.table :headers="[__('ui.hostel'), __('ui.room_number'), __('ui.floor'), __('ui.beds'), __('ui.occupancy'), __('ui.col_action')]">
        @forelse ($hostelRooms as $room)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $room->hostel?->name ?: '—' }}</td>
                <td class="px-4 py-3">{{ $room->room_number }}</td>
                <td class="px-4 py-3 text-muted">{{ $room->floor ?: '—' }}</td>
                <td class="px-4 py-3 text-sm">
                    @foreach ($room->beds as $bed)
                        <span class="me-2 inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-xs dark:bg-slate-800">
                            {{ $bed->label }} · {{ __('ui.bed_status_'.$bed->status) }}
                        </span>
                    @endforeach
                </td>
                <td class="px-4 py-3">{{ $room->occupied_beds_count }} / {{ $room->beds_count }}</td>
                <td class="px-4 py-3">
                    @can('hostel.room.manage')
                        <form method="POST" action="{{ route('tenant.hostel.rooms.destroy', $room) }}" onsubmit="return confirm(@json(__('ui.confirm_delete')))">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $hostelRooms->links() }}</div>
@endsection
