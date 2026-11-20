@extends('layouts.app')

@section('title', __('ui.nav.curricula'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 04</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $curriculum->name }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.curricula_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('tenant.curricula.update', $curriculum) }}" class="mb-8 max-w-2xl space-y-4">
        @csrf
        @method('PUT')
        <x-ui.form-section :title="__('ui.curriculum_details')">
            <div class="space-y-4">
                <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name', $curriculum->name)" required />
                <x-ui.input name="code" :label="__('ui.code')" :value="old('code', $curriculum->code)" />
                <x-ui.input name="board" :label="__('ui.board')" :value="old('board', $curriculum->board)" />
                <x-ui.textarea name="description" :label="__('ui.description')">{{ old('description', $curriculum->description) }}</x-ui.textarea>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $curriculum->is_default))> {{ __('ui.default') }}</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $curriculum->is_active))> {{ __('ui.status_active') }}</label>
            </div>
            <div class="mt-6"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button></div>
        </x-ui.form-section>
    </form>

    <x-ui.form-section :title="__('ui.curriculum_subjects')" class="mb-8">
        <x-ui.table :headers="[__('ui.nav.subjects'), __('ui.nav.classes'), __('ui.compulsory'), __('ui.col_action')]">
            @forelse ($curriculum->subjectLinks as $link)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $link->subject?->name }}</td>
                    <td class="px-4 py-3 text-muted">{{ $link->schoolClass?->name ?: '—' }}</td>
                    <td class="px-4 py-3">{{ $link->is_compulsory ? __('ui.yes') : __('ui.no') }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('tenant.curricula.detach-subject', [$curriculum, $link]) }}" onsubmit="return confirm('{{ __('ui.confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
            @endforelse
        </x-ui.table>

        <form method="POST" action="{{ route('tenant.curricula.attach-subject', $curriculum) }}" class="mt-6 grid gap-4 md:grid-cols-4">
            @csrf
            <x-ui.select name="subject_id" :label="__('ui.nav.subjects')" required>
                <option value="">{{ __('ui.select_option') }}</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select name="school_class_id" :label="__('ui.nav.classes')">
                <option value="">{{ __('ui.select_option') }}</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input name="weekly_periods" type="number" :label="__('ui.weekly_periods')" />
            <div class="flex items-end"><x-ui.button type="submit">{{ __('ui.attach_subject') }}</x-ui.button></div>
        </form>
    </x-ui.form-section>
@endsection
