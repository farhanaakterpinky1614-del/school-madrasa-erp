@extends('layouts.app')

@section('title', __('ui.nav.fee_structures'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 11</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.fee_structures') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.fee_structures_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
            <ul class="list-disc ps-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.fee_category') }}</label>
            <select name="category_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all') }}</option>
                @foreach ($allCategories as $cat)
                    <option value="{{ $cat->id }}" @selected((int) $filterCategoryId === (int) $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.academic_year') }}</label>
            <select name="year_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all') }}</option>
                @foreach ($years as $year)
                    <option value="{{ $year->id }}" @selected((int) $filterYearId === (int) $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    @can('fees.structure.manage')
        <form method="POST" action="{{ route('tenant.fees.structures.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 md:grid-cols-3">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_name') }}</label>
                <input name="name" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.fee_category') }}</label>
                <select name="fee_category_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                    @foreach ($allCategories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.academic_year') }}</label>
                <select name="academic_year_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                    @foreach ($years as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.class') }}</label>
                <select name="school_class_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                    <option value="">{{ __('ui.all_classes') }}</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.amount_bdt') }}</label>
                <input type="number" step="0.01" min="0" name="amount" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.frequency') }}</label>
                <select name="frequency" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                    @foreach ($frequencies as $freq)
                        <option value="{{ $freq }}">{{ __('ui.fee_freq_'.$freq) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3">
                <x-ui.button type="submit">{{ __('ui.add_fee_structure') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <x-ui.table :headers="[__('ui.col_name'), __('ui.fee_category'), __('ui.class'), __('ui.amount_bdt'), __('ui.frequency'), __('ui.col_action')]">
        @forelse ($structuresList as $structure)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $structure->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $structure->category?->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $structure->schoolClass?->name ?? __('ui.all_classes') }}</td>
                <td class="px-4 py-3 font-medium">{{ \Modules\Fees\Support\Money::formatBdt($structure->amount) }}</td>
                <td class="px-4 py-3">{{ __('ui.fee_freq_'.$structure->frequency) }}</td>
                <td class="px-4 py-3 space-y-2">
                    @can('fees.invoice.manage')
                        <form method="POST" action="{{ route('tenant.fees.structures.assign', $structure) }}" class="flex flex-wrap items-end gap-2">
                            @csrf
                            <select name="student_id" class="rounded-lg border border-slate-200 px-2 py-1.5 text-xs dark:border-slate-600 dark:bg-slate-950">
                                <option value="">{{ __('ui.select_student') }}</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->full_name }}</option>
                                @endforeach
                            </select>
                            @if ($structure->school_class_id)
                                <label class="flex items-center gap-1 text-xs text-muted">
                                    <input type="checkbox" name="assign_class" value="1"> {{ __('ui.assign_whole_class') }}
                                </label>
                            @endif
                            <label class="flex items-center gap-1 text-xs text-muted">
                                <input type="checkbox" name="issue_now" value="1" checked> {{ __('ui.issue_now') }}
                            </label>
                            <button type="submit" class="text-xs font-semibold text-primary-600">{{ __('ui.generate_invoice') }}</button>
                        </form>
                    @endcan
                    @can('fees.structure.manage')
                        <form method="POST" action="{{ route('tenant.fees.structures.destroy', $structure) }}" onsubmit="return confirm(@json(__('ui.confirm_delete')))">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $structuresList->links() }}</div>
@endsection
