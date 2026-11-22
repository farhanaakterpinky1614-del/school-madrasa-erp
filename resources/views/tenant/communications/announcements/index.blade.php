@extends('layouts.app')

@section('title', __('ui.nav.announcements'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 14</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.announcements') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.announcements_help') }}</p>
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
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.status') }}</label>
            <select name="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">{{ __('ui.all') }}</option>
                @foreach ($announcementStatuses as $st)
                    <option value="{{ $st }}" @selected($filterStatus === $st)>{{ __('ui.announcement_status_'.$st) }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.button type="submit" variant="secondary">{{ __('ui.filter') }}</x-ui.button>
    </form>

    @can('communications.announcement.manage')
        <form method="POST" action="{{ route('tenant.communications.announcements.store') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 p-4 dark:border-slate-700 dark:from-slate-900 md:grid-cols-2">
            @csrf
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.title') }}</label>
                <input name="title" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('title') }}">
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.body') }}</label>
                <textarea name="body" rows="3" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">{{ old('body') }}</textarea>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.audience_type') }}</label>
                <select name="audience_type" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    @foreach ($audienceTypes as $type)
                        <option value="{{ $type }}" @selected(old('audience_type', 'all') === $type)>{{ __('ui.audience_'.$type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.scheduled_at') }}</label>
                <input type="datetime-local" name="scheduled_at" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" value="{{ old('scheduled_at') }}">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.audience_roles_label') }}</label>
                <select name="audience_roles[]" multiple class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" size="4">
                    @foreach ($roleNames as $role)
                        <option value="{{ $role }}">{{ $role }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.school_class') }}</label>
                <select name="school_class_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    <option value="">{{ __('ui.select') }}</option>
                    @foreach ($schoolClasses as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2 flex items-end">
                <x-ui.button type="submit">{{ __('ui.add_announcement') }}</x-ui.button>
            </div>
        </form>
    @endcan

    <x-ui.table :headers="[__('ui.title'), __('ui.audience_type'), __('ui.status'), __('ui.published_at'), __('ui.col_action')]">
        @forelse ($announcements as $row)
            <tr>
                <td class="px-4 py-3">
                    <p class="font-medium">{{ $row->title }}</p>
                    <p class="mt-0.5 line-clamp-2 text-xs text-muted">{{ $row->body }}</p>
                </td>
                <td class="px-4 py-3 text-sm">
                    {{ __('ui.audience_'.$row->audience_type) }}
                    @if ($row->audience_type === 'roles' && $row->audience_roles)
                        <span class="text-muted">({{ implode(', ', $row->audience_roles) }})</span>
                    @elseif ($row->audience_type === 'class' && $row->schoolClass)
                        <span class="text-muted">({{ $row->schoolClass->name }})</span>
                    @endif
                </td>
                <td class="px-4 py-3">{{ __('ui.announcement_status_'.$row->status) }}</td>
                <td class="px-4 py-3 text-muted text-sm">{{ $row->published_at?->timezone('Asia/Dhaka')->format('Y-m-d H:i') ?: '—' }}</td>
                <td class="px-4 py-3">
                    @can('communications.announcement.manage')
                        <div class="flex flex-wrap gap-2">
                            @if ($row->status !== 'published')
                                <form method="POST" action="{{ route('tenant.communications.announcements.publish', $row) }}">
                                    @csrf
                                    <button type="submit" class="text-sm font-semibold text-indigo-600">{{ __('ui.publish') }}</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('tenant.communications.announcements.unpublish', $row) }}">
                                    @csrf
                                    <button type="submit" class="text-sm font-semibold text-amber-700">{{ __('ui.unpublish') }}</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('tenant.communications.announcements.destroy', $row) }}" onsubmit="return confirm(@json(__('ui.confirm_delete')))">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                            </form>
                        </div>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>

    <div class="mt-4">{{ $announcements->links() }}</div>
@endsection
