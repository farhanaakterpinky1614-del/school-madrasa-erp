@extends('layouts.app')

@section('title', __('ui.nav.fee_categories'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 11</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.fee_categories') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.fee_categories_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    @can('fees.category.manage')
        <form method="POST" action="{{ route('tenant.fees.categories.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-5">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_name') }}</label>
                <input name="name" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('name') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.code') }}</label>
                <input name="code" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('code') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.fee_kind') }}</label>
                <select name="kind" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($kinds as $kind)
                        <option value="{{ $kind }}">{{ __('ui.fee_kind_'.$kind) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2 flex items-end">
                <x-ui.button type="submit">{{ __('ui.add_fee_category') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <x-ui.table :headers="[__('ui.col_name'), __('ui.code'), __('ui.fee_kind'), __('ui.col_status'), __('ui.col_action')]">
        @forelse ($categories as $category)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $category->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $category->code }}</td>
                <td class="px-4 py-3">{{ __('ui.fee_kind_'.$category->kind) }}</td>
                <td class="px-4 py-3">{{ $category->is_active ? __('ui.status_active') : __('ui.inactive') }}</td>
                <td class="px-4 py-3">
                    @can('fees.category.manage')
                        <form method="POST" action="{{ route('tenant.fees.categories.destroy', $category) }}" onsubmit="return confirm(@json(__('ui.confirm_delete')))">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $categories->links() }}</div>
@endsection
