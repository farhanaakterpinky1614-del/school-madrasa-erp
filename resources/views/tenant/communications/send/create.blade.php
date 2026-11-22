@extends('layouts.app')

@section('title', __('ui.nav.send_notification'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 14</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.send_notification') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.send_notification_help') }}</p>
    </div>
@endsection

@section('content')
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
            <ul class="list-disc ps-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tenant.communications.send.store') }}" class="grid max-w-3xl gap-4 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-5 dark:border-slate-700 dark:from-slate-900">
        @csrf
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.template') }}</label>
            <select name="notification_template_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.select') }}</option>
                @foreach ($templates as $tpl)
                    <option value="{{ $tpl->id }}" @selected((string) old('notification_template_id') === (string) $tpl->id)>
                        {{ $tpl->name }} ({{ __('ui.channel_'.$tpl->channel) }} · {{ $tpl->code }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.recipient') }}</label>
            <input name="recipient" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('recipient') }}" placeholder="017XXXXXXXX / email@example.com">
        </div>
        <div class="grid gap-3 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">@{{student_name}}</label>
                <input name="student_name" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('student_name') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">@{{amount}}</label>
                <input name="amount" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('amount') }}">
            </div>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">@{{institution_name}}</label>
            <input name="institution_name" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('institution_name') }}">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.extra_placeholders') }}</label>
            <textarea name="extra_placeholders" rows="3" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-mono dark:border-slate-600 dark:bg-slate-900" placeholder="key=value">{{ old('extra_placeholders') }}</textarea>
        </div>
        <div>
            <x-ui.button type="submit">{{ __('ui.send_now') }}</x-ui.button>
        </div>
    </form>
@endsection
