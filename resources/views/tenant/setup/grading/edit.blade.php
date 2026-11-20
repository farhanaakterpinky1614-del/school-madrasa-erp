@extends('layouts.app')

@section('title', __('ui.nav.grading'))

@section('page-header')
    <div>
        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">Phase 04</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $scale->name }}</h1>
        <p class="mt-1.5 text-sm text-muted">{{ __('ui.grading_help') }}</p>
    </div>
@endsection

@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('tenant.grading.update', $scale) }}" class="mb-8 max-w-2xl space-y-4">
        @csrf
        @method('PUT')
        <x-ui.form-section :title="__('ui.nav.grading')">
            <div class="space-y-4">
                <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name', $scale->name)" required />
                <x-ui.input name="code" :label="__('ui.code')" :value="old('code', $scale->code)" />
                <x-ui.textarea name="description" :label="__('ui.description')">{{ old('description', $scale->description) }}</x-ui.textarea>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $scale->is_default))> {{ __('ui.default') }}</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $scale->is_active))> {{ __('ui.status_active') }}</label>
            </div>
            <div class="mt-6"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button></div>
        </x-ui.form-section>
    </form>

    <x-ui.form-section :title="__('ui.grading_bands')">
        <x-ui.table :headers="[__('ui.grade'), __('ui.min_mark'), __('ui.max_mark'), __('ui.gpa'), __('ui.col_action')]">
            @forelse ($scale->bands as $band)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $band->grade }}</td>
                    <td class="px-4 py-3">{{ $band->min_mark }}</td>
                    <td class="px-4 py-3">{{ $band->max_mark }}</td>
                    <td class="px-4 py-3">{{ $band->gpa }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('tenant.grading.destroy-band', [$scale, $band]) }}" onsubmit="return confirm('{{ __('ui.confirm') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-rose-600">{{ __('ui.delete') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-muted">{{ __('ui.no_records') }}</td></tr>
            @endforelse
        </x-ui.table>

        <form method="POST" action="{{ route('tenant.grading.store-band', $scale) }}" class="mt-6 grid gap-4 md:grid-cols-5">
            @csrf
            <x-ui.input name="grade" :label="__('ui.grade')" required />
            <x-ui.input name="min_mark" type="number" step="0.01" :label="__('ui.min_mark')" required />
            <x-ui.input name="max_mark" type="number" step="0.01" :label="__('ui.max_mark')" required />
            <x-ui.input name="gpa" type="number" step="0.01" :label="__('ui.gpa')" />
            <div class="flex items-end"><x-ui.button type="submit">{{ __('ui.add_band') }}</x-ui.button></div>
        </form>
    </x-ui.form-section>
@endsection
