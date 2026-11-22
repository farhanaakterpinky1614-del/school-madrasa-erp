@extends('layouts.app')

@section('title', __('ui.nav.notification_templates'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 14</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.notification_templates') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.notification_templates_help') }}</p>
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
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.channel') }}</label>
            <select name="channel" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all') }}</option>
                @foreach ($templateChannels as $ch)
                    <option value="{{ $ch }}" @selected($filterChannel === $ch)>{{ __('ui.channel_'.$ch) }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    @can('communications.template.manage')
        <form method="POST" action="{{ route('tenant.communications.templates.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-2">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.name') }}</label>
                <input name="name" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('name') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.code') }}</label>
                <input name="code" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('code') }}" placeholder="FEE_DUE_SMS">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.channel') }}</label>
                <select name="channel" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($templateChannels as $ch)
                        <option value="{{ $ch }}">{{ __('ui.channel_'.$ch) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.subject') }}</label>
                <input name="subject" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('subject') }}" placeholder="{{ __('ui.email_only') }}">
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.body') }}</label>
                <textarea name="body" rows="3" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" placeholder="{{ __('ui.placeholders_example') }}">{{ old('body') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.placeholders_help') }}</label>
                <input name="placeholders_help" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('placeholders_help', __('ui.placeholders_help_default')) }}">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" checked id="tpl_active" class="rounded border-slate-300">
                <label for="tpl_active" class="text-sm">{{ __('ui.active') }}</label>
            </div>
            <div class="flex items-end">
                <x-ui.button type="submit">{{ __('ui.add_template') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <x-ui.table :headers="[__('ui.name'), __('ui.code'), __('ui.channel'), __('ui.status'), __('ui.col_action')]">
        @forelse ($notificationTemplates as $tpl)
            <tr>
                <td class="px-4 py-3">
                    <p class="font-medium">{{ $tpl->name }}</p>
                    <p class="mt-0.5 line-clamp-2 text-xs text-muted">{{ $tpl->body }}</p>
                </td>
                <td class="px-4 py-3 font-mono text-sm">{{ $tpl->code }}</td>
                <td class="px-4 py-3">{{ __('ui.channel_'.$tpl->channel) }}</td>
                <td class="px-4 py-3">{{ $tpl->is_active ? __('ui.active') : __('ui.inactive') }}</td>
                <td class="px-4 py-3">
                    @can('communications.template.manage')
                        <form method="POST" action="{{ route('tenant.communications.templates.destroy', $tpl) }}" onsubmit="return confirm(@json(__('ui.confirm_delete')))">
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

    <div class="mt-4">{{ $notificationTemplates->links() }}</div>
@endsection
