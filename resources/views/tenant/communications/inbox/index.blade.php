@extends('layouts.app')

@section('title', __('ui.nav.inbox'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 14</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.inbox') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.inbox_help') }}</p>
    </div>
@endsection

@section('content')
    <div class="space-y-3">
        @forelse ($items as $item)
            <article class="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/30 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <h2 class="text-base font-semibold tracking-tight">{{ $item->title }}</h2>
                    <time class="text-xs text-muted">{{ $item->published_at?->timezone('Asia/Dhaka')->format('Y-m-d H:i') }}</time>
                </div>
                <p class="mt-2 whitespace-pre-line text-sm text-slate-700 dark:text-slate-200">{{ $item->body }}</p>
                <p class="mt-2 text-xs text-muted">
                    {{ __('ui.audience_'.$item->audience_type) }}
                    @if ($item->schoolClass)
                        · {{ $item->schoolClass->name }}
                    @endif
                </p>
            </article>
        @empty
            <p class="rounded-xl border border-dashed border-slate-300 px-4 py-10 text-center text-muted">{{ __('ui.inbox_empty') }}</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
@endsection
