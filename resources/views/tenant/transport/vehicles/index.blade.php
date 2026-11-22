@extends('layouts.app')

@section('title', __('ui.nav.transport_vehicles'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 13</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.transport_vehicles') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.transport_vehicles_help') }}</p>
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
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.search') }}</label>
            <input name="q" value="{{ $filterQ }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" placeholder="{{ __('ui.plate_number') }}">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.status') }}</label>
            <select name="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all') }}</option>
                @foreach ($vehicleStatuses as $st)
                    <option value="{{ $st }}" @selected($filterStatus === $st)>{{ __('ui.vehicle_status_'.$st) }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    @can('transport.vehicle.manage')
        <form method="POST" action="{{ route('tenant.transport.vehicles.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.plate_number') }}</label>
                <input name="plate_number" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('plate_number') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.vehicle_type') }}</label>
                <select name="type" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($vehicleTypes as $type)
                        <option value="{{ $type }}">{{ __('ui.vehicle_type_'.$type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.capacity') }}</label>
                <input type="number" name="capacity" min="1" max="200" value="{{ old('capacity', 30) }}" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.status') }}</label>
                <select name="status" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($vehicleStatuses as $st)
                        <option value="{{ $st }}">{{ __('ui.vehicle_status_'.$st) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.driver_name') }}</label>
                <input name="driver_name" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('driver_name') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.driver_phone') }}</label>
                <input name="driver_phone" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('driver_phone') }}">
            </div>
            <div class="md:col-span-2 flex items-end">
                <x-ui.button type="submit">{{ __('ui.add_vehicle') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <x-ui.table :headers="[__('ui.plate_number'), __('ui.vehicle_type'), __('ui.capacity'), __('ui.driver_name'), __('ui.driver_phone'), __('ui.status'), __('ui.col_action')]">
        @forelse ($vehicles as $vehicle)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $vehicle->plate_number }}</td>
                <td class="px-4 py-3">{{ __('ui.vehicle_type_'.$vehicle->type) }}</td>
                <td class="px-4 py-3">{{ $vehicle->capacity }}</td>
                <td class="px-4 py-3 text-muted">{{ $vehicle->driver_name ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $vehicle->driver_phone ?: '—' }}</td>
                <td class="px-4 py-3">{{ __('ui.vehicle_status_'.$vehicle->status) }}</td>
                <td class="px-4 py-3">
                    @can('transport.vehicle.manage')
                        <form method="POST" action="{{ route('tenant.transport.vehicles.destroy', $vehicle) }}" onsubmit="return confirm(@json(__('ui.confirm_delete')))">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $vehicles->links() }}</div>
@endsection
