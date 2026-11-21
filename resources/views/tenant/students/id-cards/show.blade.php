@extends('layouts.app')
@section('title', __('ui.id_card'))
@section('page-header')
<div>
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.id_card') }}</h1>
    <p class="mt-1.5 text-sm text-muted">{{ __('ui.id_card_help') }}</p>
</div>
<div class="flex flex-wrap gap-2 print:hidden">
    <button type="button" onclick="window.print()"><x-ui.button type="button">{{ __('ui.print') }}</x-ui.button></button>
</div>
@endsection
@section('content')
    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 print:hidden">{{ session('status') }}</p>
    @endif

    <div class="mx-auto max-w-md overflow-hidden rounded-2xl border border-sme bg-gradient-to-br from-slate-50 via-white to-indigo-50 shadow-sme-soft print:shadow-none">
        <div class="bg-gradient-to-r from-indigo-600 to-slate-800 px-5 py-4 text-white">
            <p class="text-xs uppercase tracking-[0.16em] text-indigo-100">{{ $institution?->name ?? config('app.name') }}</p>
            <p class="mt-1 text-lg font-semibold">{{ __('ui.student_id_card') }}</p>
        </div>

        <div class="flex gap-4 p-5">
            <div class="shrink-0">
                @if ($student->photo_path)
                    <img
                        src="{{ route('tenant.students.photo.show', $student) }}"
                        alt="{{ $student->full_name }}"
                        class="h-28 w-24 rounded-xl border border-sme object-cover bg-white"
                    />
                @else
                    <div class="flex h-28 w-24 flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-100/80 text-center text-[10px] font-medium uppercase tracking-wide text-slate-500">
                        <span>{{ __('ui.photo_placeholder') }}</span>
                    </div>
                @endif
            </div>

            <div class="min-w-0 flex-1 space-y-1.5 text-sm">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('ui.col_name') }}</p>
                <p class="text-2xl font-semibold leading-tight tracking-tight text-slate-900">{{ $student->full_name }}</p>
                @if ($student->name_bn)
                    <p class="text-sm text-slate-600">{{ $student->name_bn }}</p>
                @endif
                <p class="pt-1 font-mono text-sm font-semibold text-primary-700">{{ $student->student_code }}</p>
                <p class="text-muted">
                    {{ $student->currentEnrollment?->schoolClass?->name }}
                    @if ($student->currentEnrollment?->section)
                        · {{ $student->currentEnrollment->section->name }}
                    @endif
                </p>
                <p class="text-muted">{{ __('ui.admission_number') }}: {{ $student->admission_number ?: '—' }}</p>
            </div>

            <img src="{{ $qrDataUri }}" alt="QR" class="h-24 w-24 shrink-0 rounded-xl border border-sme bg-white p-1" />
        </div>

        <p class="border-t border-sme px-5 py-3 text-xs text-muted">{{ $student->qr_payload }}</p>
    </div>

    <div class="mx-auto mt-6 max-w-md print:hidden">
        <x-ui.form-section :title="__('ui.student_photo')">
            <div class="flex items-start gap-4">
                @if ($student->photo_path)
                    <img
                        src="{{ route('tenant.students.photo.show', $student) }}"
                        alt="{{ $student->full_name }}"
                        class="h-20 w-16 rounded-lg border border-sme object-cover"
                    />
                @else
                    <div class="flex h-20 w-16 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 text-[10px] text-slate-500">{{ __('ui.photo_placeholder') }}</div>
                @endif
                <div class="flex-1 space-y-3">
                    <form method="POST" action="{{ route('tenant.students.photo.store', $student) }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="hidden" name="redirect_to" value="id-card" />
                        <div>
                            <label class="mb-1 block text-sm font-medium">{{ $student->photo_path ? __('ui.change_photo') : __('ui.add_photo') }}</label>
                            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required class="block w-full text-sm" />
                            @error('photo')<p class="mt-1 text-xs text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <x-ui.button type="submit" size="sm">{{ $student->photo_path ? __('ui.change_photo') : __('ui.add_photo') }}</x-ui.button>
                    </form>
                    @if ($student->photo_path)
                        <form method="POST" action="{{ route('tenant.students.photo.destroy', $student) }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="redirect_to" value="id-card" />
                            <x-ui.button type="submit" size="sm" variant="outline">{{ __('ui.remove_photo') }}</x-ui.button>
                        </form>
                    @endif
                </div>
            </div>
        </x-ui.form-section>
    </div>
@endsection
