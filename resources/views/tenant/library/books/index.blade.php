@extends('layouts.app')

@section('title', __('ui.nav.library_books'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 12</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.library_books') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.library_books_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.search') }}</label>
            <input name="q" value="{{ $filterQ }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" placeholder="{{ __('ui.title') }} / ISBN">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.language') }}</label>
            <select name="language" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all_languages') }}</option>
                @foreach ($languages as $lang)
                    <option value="{{ $lang }}" @selected($filterLanguage === $lang)>{{ __('ui.lang_'.$lang) }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    @can('library.book.manage')
        <form method="POST" action="{{ route('tenant.library.books.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-4">
            @csrf
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.title') }}</label>
                <input name="title" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('title') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.author') }}</label>
                <input name="author" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('author') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.isbn') }}</label>
                <input name="isbn" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('isbn') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.category') }}</label>
                <input name="category" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('category') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.subject_tags') }}</label>
                <input name="subject_tags" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('subject_tags') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.publisher') }}</label>
                <input name="publisher" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('publisher') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.language') }}</label>
                <select name="language" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($languages as $lang)
                        <option value="{{ $lang }}">{{ __('ui.lang_'.$lang) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4 flex items-end">
                <x-ui.button type="submit">{{ __('ui.add_library_book') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <x-ui.table :headers="[__('ui.title'), __('ui.author'), __('ui.isbn'), __('ui.category'), __('ui.language'), __('ui.copies'), __('ui.col_action')]">
        @forelse ($books as $book)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $book->title }}</td>
                <td class="px-4 py-3 text-muted">{{ $book->author ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $book->isbn ?: '—' }}</td>
                <td class="px-4 py-3">{{ $book->category ?: '—' }}</td>
                <td class="px-4 py-3">{{ __('ui.lang_'.$book->language) }}</td>
                <td class="px-4 py-3">{{ $book->available_copies_count }} / {{ $book->copies_count }}</td>
                <td class="px-4 py-3">
                    @can('library.book.manage')
                        <form method="POST" action="{{ route('tenant.library.books.destroy', $book) }}" onsubmit="return confirm(@json(__('ui.confirm_delete')))">
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
    <div class="mt-4">{{ $books->links() }}</div>
@endsection
