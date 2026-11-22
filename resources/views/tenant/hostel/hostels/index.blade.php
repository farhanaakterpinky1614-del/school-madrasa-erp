@extends('layouts.app')

@section('title', __('ui.nav.hostels'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 13</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.hostels') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.hostels_help') }}</p>
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
            <input name="q" value="{{ $filterQ }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    @can('hostel.hostel.manage')
        <form method="POST" action="{{ route('tenant.hostel.hostels.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-3">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.name') }}</label>
                <input name="name" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('name') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.code') }}</label>
                <input name="code" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('code') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.gender_type') }}</label>
                <select name="gender_type" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($genderTypes as $gt)
                        <option value="{{ $gt }}">{{ __('ui.hostel_gender_'.$gt) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.capacity_notes') }}</label>
                <input name="capacity_notes" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('capacity_notes') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.monthly_fee_bdt') }}</label>
                <input type="number" step="0.01" min="0" name="monthly_fee_amount" value="{{ old('monthly_fee_amount') }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.fee_category_optional') }}</label>
                <select name="fee_category_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    <option value="">{{ __('ui.optional') }}</option>
                    @foreach ($feeCategories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.fee_structure_optional') }}</label>
                <select name="fee_structure_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    <option value="">{{ __('ui.optional') }}</option>
                    @foreach ($feeStructures as $structure)
                        <option value="{{ $structure->id }}">{{ $structure->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <x-ui.button type="submit">{{ __('ui.add_hostel') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <x-ui.table :headers="[__('ui.name'), __('ui.code'), __('ui.gender_type'), __('ui.capacity_notes'), __('ui.monthly_fee_bdt'), __('ui.rooms'), __('ui.fee_category_optional'), __('ui.col_action')]">
        @forelse ($hostelList as $hostel)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $hostel->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $hostel->code ?: '—' }}</td>
                <td class="px-4 py-3">{{ __('ui.hostel_gender_'.$hostel->gender_type) }}</td>
                <td class="px-4 py-3 text-sm text-muted">{{ $hostel->capacity_notes ?: '—' }}</td>
                <td class="px-4 py-3">{{ $hostel->monthly_fee_amount !== null ? \Modules\Fees\Support\Money::formatBdt($hostel->monthly_fee_amount) : '—' }}</td>
                <td class="px-4 py-3">{{ $hostel->rooms_count }}</td>
                <td class="px-4 py-3 text-muted">{{ $hostel->feeCategory?->name ?: '—' }}</td>
                <td class="px-4 py-3">
                    @can('hostel.hostel.manage')
                        <form method="POST" action="{{ route('tenant.hostel.hostels.destroy', $hostel) }}" onsubmit="return confirm(@json(__('ui.confirm_delete')))">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $hostelList->links() }}</div>
@endsection
