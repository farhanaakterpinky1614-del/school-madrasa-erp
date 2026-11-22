@extends('layouts.app')

@section('title', __('ui.nav.library_copies'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 12</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.library_copies') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.library_copies_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_status') }}</label>
            <select name="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all_statuses') }}</option>
                @foreach ($copyStatuses as $st)
                    <option value="{{ $st }}" @selected($filterStatus === $st)>{{ __('ui.copy_status_'.$st) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.book') }}</label>
            <select name="book_id" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all_books') }}</option>
                @foreach ($books as $book)
                    <option value="{{ $book->id }}" @selected((int) $filterBookId === (int) $book->id)>{{ $book->title }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    @can('library.copy.manage')
        <form method="POST" action="{{ route('tenant.library.copies.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-3">
            @csrf
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.book') }}</label>
                <select name="library_book_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($books as $book)
                        <option value="{{ $book->id }}">{{ $book->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.accession_no') }}</label>
                <input name="accession_no" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('accession_no') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.barcode') }}</label>
                <input name="barcode" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('barcode') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.condition') }}</label>
                <select name="condition" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($conditions as $cond)
                        <option value="{{ $cond }}">{{ __('ui.copy_condition_'.$cond) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_status') }}</label>
                <select name="status" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($copyStatuses as $st)
                        <option value="{{ $st }}">{{ __('ui.copy_status_'.$st) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.shelf_location') }}</label>
                <input name="shelf_location" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('shelf_location') }}">
            </div>
            <div class="md:col-span-3 flex items-end">
                <x-ui.button type="submit">{{ __('ui.add_library_copy') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <x-ui.table :headers="[__('ui.accession_no'), __('ui.book'), __('ui.barcode'), __('ui.condition'), __('ui.col_status'), __('ui.shelf_location'), __('ui.col_action')]">
        @forelse ($copies as $copy)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $copy->accession_no }}</td>
                <td class="px-4 py-3">{{ $copy->book?->title }}</td>
                <td class="px-4 py-3 text-muted">{{ $copy->barcode ?: '—' }}</td>
                <td class="px-4 py-3">{{ __('ui.copy_condition_'.$copy->condition) }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex rounded-lg bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                        {{ __('ui.copy_status_'.$copy->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-muted">{{ $copy->shelf_location ?: '—' }}</td>
                <td class="px-4 py-3">
                    @can('library.copy.manage')
                        @if ($copy->status !== 'issued')
                            <form method="POST" action="{{ route('tenant.library.copies.destroy', $copy) }}" onsubmit="return confirm(@json(__('ui.confirm_delete')))">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                            </form>
                        @endif
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $copies->links() }}</div>
@endsection
