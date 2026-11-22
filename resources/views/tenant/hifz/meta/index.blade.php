@extends('layouts.app')

@section('title', __('ui.nav.islamic_meta'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600">Phase 10</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.nav.islamic_meta') }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.islamic_meta_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    @can('hifz.meta.manage')
        <form method="POST" action="{{ route('tenant.hifz.meta.store') }}" class="mb-8 max-w-3xl space-y-4 rounded-2xl border border-slate-200/80 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            @csrf
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.add_islamic_meta') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.nav.subjects') }}</label>
                    <select name="subject_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" required>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }} @if($subject->subject_type)({{ $subject->subject_type }})@endif</option>
                        @endforeach
                    </select>
                </div>
                <x-ui.input name="kitab_name" :label="__('ui.kitab_name')" :value="old('kitab_name')" />
                <x-ui.input name="department_label" :label="__('ui.department_label')" :value="old('department_label')" />
                <x-ui.input name="qawmi_level" :label="__('ui.qawmi_level')" :value="old('qawmi_level')" />
                <div class="sm:col-span-2">
                    <x-ui.textarea name="notes" :label="__('ui.notes')">{{ old('notes') }}</x-ui.textarea>
                </div>
            </div>
            <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
        </form>
    @endcan

    <x-ui.table :headers="[__('ui.nav.subjects'), __('ui.kitab_name'), __('ui.department_label'), __('ui.qawmi_level'), __('ui.col_action')]">
        @forelse ($metas as $meta)
            <tr>
                <td class="px-4 py-3 font-medium">{{ $meta->subject?->name }}</td>
                <td class="px-4 py-3 text-muted">{{ $meta->kitab_name ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $meta->department_label ?: '—' }}</td>
                <td class="px-4 py-3 text-muted">{{ $meta->qawmi_level ?: '—' }}</td>
                <td class="px-4 py-3">
                    @can('hifz.meta.manage')
                        <form method="POST" action="{{ route('tenant.hifz.meta.destroy', $meta) }}" onsubmit="return confirm('{{ __('ui.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
        @endforelse
    </x-ui.table>
    <div class="mt-4">{{ $metas->links() }}</div>
@endsection
