@extends('layouts.app')

@section('title', __('ui.nav.expenses'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 11</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.expenses') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.expenses_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <p class="mb-4 rounded-xl border border-slate-200 bg-indigo-50/60 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
        {{ __('ui.period_total') }}: <strong>{{ \Modules\Fees\Support\Money::formatBdt($periodTotal) }}</strong>
    </p>

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.expense_category') }}</label>
            <select name="category_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all') }}</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((int) $filterCategoryId === (int) $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.date_from') }}</label>
            <input type="date" name="from" value="{{ $filterFrom }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.date_to') }}</label>
            <input type="date" name="to" value="{{ $filterTo }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    @can('finance.expense.manage')
        <form method="POST" action="{{ route('tenant.finance.expenses.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-700 dark:bg-slate-900 md:grid-cols-3">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.title') }}</label>
                <input name="title" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.expense_category') }}</label>
                <select name="expense_category_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.amount_bdt') }}</label>
                <input type="number" step="0.01" min="0.01" name="amount" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.spent_on') }}</label>
                <input type="date" name="spent_on" required value="{{ now('Asia/Dhaka')->toDateString() }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.payment_method') }}</label>
                <select name="payment_method" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                    @foreach ($methods as $method)
                        <option value="{{ $method }}">{{ __('ui.pay_method_'.$method) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <x-ui.button type="submit">{{ __('ui.add_expense') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <x-ui.table :headers="[__('ui.title'), __('ui.expense_category'), __('ui.amount_bdt'), __('ui.spent_on'), __('ui.payment_method'), __('ui.col_action')]">
        @forelse ($expenses as $expense)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $expense->title }}</td>
                <td class="px-4 py-3 text-muted">{{ $expense->category?->name }}</td>
                <td class="px-4 py-3">{{ \Modules\Fees\Support\Money::formatBdt($expense->amount) }}</td>
                <td class="px-4 py-3 text-muted">{{ $expense->spent_on?->format('Y-m-d') }}</td>
                <td class="px-4 py-3">{{ __('ui.pay_method_'.$expense->payment_method) }}</td>
                <td class="px-4 py-3">
                    @can('finance.expense.manage')
                        <form method="POST" action="{{ route('tenant.finance.expenses.destroy', $expense) }}" onsubmit="return confirm(@json(__('ui.confirm_delete')))">
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
    <div class="mt-4">{{ $expenses->links() }}</div>
@endsection
