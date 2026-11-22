@extends('layouts.app')

@section('title', __('ui.new_invoice'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 11</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.new_invoice') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.fee_invoices_help') }}</p>
    </div>
    <a href="{{ route('tenant.fees.invoices.index') }}" class="text-sm font-semibold text-primary-600">{{ __('ui.back') }}</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('tenant.fees.invoices.store') }}" class="max-w-2xl space-y-4 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/30 to-slate-50 p-6 dark:border-slate-700 dark:from-slate-900">
        @csrf
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_student') }}</label>
            <select name="student_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                <option value="">{{ __('ui.select_student') }}</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->full_name }} ({{ $student->student_code }})</option>
                @endforeach
            </select>
            @error('student_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.fee_structure') }}</label>
            <select name="fee_structure_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
                <option value="">{{ __('ui.manual_amount') }}</option>
                @foreach ($structures as $structure)
                    <option value="{{ $structure->id }}" @selected(old('fee_structure_id') == $structure->id)>
                        {{ $structure->name }} — {{ \Modules\Fees\Support\Money::formatBdt($structure->amount) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.title') }}</label>
            <input name="title" required value="{{ old('title') }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.amount_bdt') }}</label>
                <input type="number" step="0.01" min="0" name="subtotal" required value="{{ old('subtotal') }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.due_date') }}</label>
                <input type="date" name="due_date" value="{{ old('due_date', now('Asia/Dhaka')->addDays(14)->toDateString()) }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">
            </div>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.field_notes') }}</label>
            <textarea name="notes" rows="3" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950">{{ old('notes') }}</textarea>
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
            <input type="checkbox" name="issue_now" value="1" @checked(old('issue_now', true))> {{ __('ui.issue_now') }}
        </label>
        <div class="flex gap-3">
            <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
            <a href="{{ route('tenant.fees.invoices.index') }}"><x-ui.button type="button" variant="secondary">{{ __('ui.cancel') }}</x-ui.button></a>
        </div>
    </form>
@endsection
