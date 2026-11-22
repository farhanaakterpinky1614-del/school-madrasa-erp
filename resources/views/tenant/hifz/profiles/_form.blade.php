@php
    $profile = $profile ?? null;
@endphp

<x-ui.form-section :title="__('ui.hifz_profile_details')">
    <div class="grid gap-4 sm:grid-cols-2">
        @unless ($profile)
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_student') }}</label>
                <select name="student_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" required>
                    <option value="">—</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected((int) old('student_id') === (int) $student->id)>
                            {{ $student->full_name }} ({{ $student->student_code }})
                        </option>
                    @endforeach
                </select>
            </div>
        @endunless
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_status') }}</label>
            <select name="status" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" required>
                @foreach ($statuses as $st)
                    <option value="{{ $st }}" @selected(old('status', $profile?->status ?? 'active') === $st)>{{ __('ui.hifz_status_'.$st) }}</option>
                @endforeach
            </select>
        </div>
        @unless ($scoped ?? false)
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.assigned_ustaz') }}</label>
                <select name="assigned_teacher_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                    <option value="">—</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((int) old('assigned_teacher_id', $profile?->assigned_teacher_id) === (int) $teacher->id)>{{ $teacher->full_name }}</option>
                    @endforeach
                </select>
            </div>
        @endunless
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.current_juz_para') }}</label>
            <select name="current_juz" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" required>
                @foreach ($juzOptions as $juz)
                    <option value="{{ $juz }}" @selected((int) old('current_juz', $profile?->current_juz ?? 1) === (int) $juz)>{{ $juz }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.input name="current_ayah" type="number" min="1" max="300" :label="__('ui.current_ayah')" :value="old('current_ayah', $profile?->current_ayah ?? 1)" required />
        @unless ($scoped ?? false)
            <x-ui.input name="started_on" type="date" :label="__('ui.started_on')" :value="old('started_on', $profile?->started_on?->format('Y-m-d'))" />
            <x-ui.input name="completed_on" type="date" :label="__('ui.completed_on')" :value="old('completed_on', $profile?->completed_on?->format('Y-m-d'))" />
        @endunless
        <div class="sm:col-span-2">
            <x-ui.textarea name="notes" :label="__('ui.notes')">{{ old('notes', $profile?->notes) }}</x-ui.textarea>
        </div>
    </div>
</x-ui.form-section>
