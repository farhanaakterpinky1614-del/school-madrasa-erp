@extends('layouts.app')

@section('title', __('ui.nav.grading_schemes'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 09</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.grading_schemes') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.grading_schemes_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <x-ui.table :headers="[__('ui.col_name'), __('ui.wrapped_scale'), __('ui.grade_bands'), __('ui.col_action')]">
        @forelse ($schemes as $scheme)
            <tr>
                <td class="px-4 py-3 font-medium">
                    {{ $scheme->name }}
                    @if ($scheme->is_default)<x-ui.badge tone="info">{{ __('ui.default') }}</x-ui.badge>@endif
                </td>
                <td class="px-4 py-3 text-muted">{{ $scheme->gradingScale?->name ?? '—' }}</td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-1">
                        @foreach ($scheme->gradingScale?->bands ?? [] as $band)
                            <span class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800">{{ $band->grade }}</span>
                        @endforeach
                    </div>
                </td>
                <td class="px-4 py-3">
                    @can('exams.exam.manage')
                        <form method="POST" action="{{ route('tenant.grading-schemes.destroy', $scheme) }}" onsubmit="return confirm('{{ __('ui.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $schemes->links() }}</div>

    @can('exams.exam.manage')
        <form method="POST" action="{{ route('tenant.grading-schemes.store') }}" class="mt-8 max-w-2xl space-y-4 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-indigo-50/30 to-slate-50 p-5 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
            @csrf
            <h2 class="text-base font-semibold">{{ __('ui.new_grading_scheme') }}</h2>
            <x-ui.input name="name" :label="__('ui.col_name')" required />
            <x-ui.input name="code" :label="__('ui.code')" />
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.wrap_grading_scale') }}</label>
                <select name="grading_scale_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" required>
                    @foreach ($scales as $scale)
                        <option value="{{ $scale->id }}">{{ $scale->name }}</option>
                    @endforeach
                </select>
            </div>
            <x-ui.textarea name="description" :label="__('ui.description')" />
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1"> {{ __('ui.default') }}</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> {{ __('ui.status_active') }}</label>
            <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        </form>
    @endcan
@endsection
