@php
    $exam = $exam ?? null;
    $boardMeta = old('board_meta', $exam?->board_meta ?? []);
@endphp

<x-ui.form-section :title="__('ui.exam_details')">
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name', $exam?->name)" required />
        </div>
        <x-ui.input name="code" :label="__('ui.code')" :value="old('code', $exam?->code)" />
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.col_status') }}</label>
            <select name="status" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" required>
                @foreach ($statuses as $st)
                    <option value="{{ $st }}" @selected(old('status', $exam?->status ?? 'draft') === $st)>{{ __('ui.exam_status_'.$st) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.nav.years') }}</label>
            <select name="academic_year_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">—</option>
                @foreach ($years as $year)
                    <option value="{{ $year->id }}" @selected((int) old('academic_year_id', $exam?->academic_year_id) === (int) $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.nav.terms') }}</label>
            <select name="academic_term_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">—</option>
                @foreach ($terms as $term)
                    <option value="{{ $term->id }}" @selected((int) old('academic_term_id', $exam?->academic_term_id) === (int) $term->id)>{{ $term->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.nav.classes') }}</label>
            <select name="school_class_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900" required>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" @selected((int) old('school_class_id', $exam?->school_class_id) === (int) $class->id)>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('ui.nav.grading_schemes') }}</label>
            <select name="grading_scheme_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900">
                <option value="">—</option>
                @foreach ($schemes as $scheme)
                    <option value="{{ $scheme->id }}" @selected((int) old('grading_scheme_id', $exam?->grading_scheme_id) === (int) $scheme->id)>{{ $scheme->name }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.input name="starts_on" type="date" :label="__('ui.starts_on')" :value="old('starts_on', $exam?->starts_on?->format('Y-m-d'))" />
        <x-ui.input name="ends_on" type="date" :label="__('ui.ends_on')" :value="old('ends_on', $exam?->ends_on?->format('Y-m-d'))" />
        <div class="sm:col-span-2">
            <x-ui.textarea name="notes" :label="__('ui.notes')">{{ old('notes', $exam?->notes) }}</x-ui.textarea>
        </div>
    </div>
</x-ui.form-section>

<x-ui.form-section :title="__('ui.board_exam_metadata')" class="mt-4">
    <p class="mb-4 text-sm text-muted">{{ __('ui.board_exam_metadata_help') }}</p>
    <div class="grid gap-4 sm:grid-cols-2">
        <x-ui.input name="board_name" :label="__('ui.board_name')" :value="old('board_name', $exam?->board_name)" />
        <x-ui.input name="board_roll_prefix" :label="__('ui.board_roll_prefix')" :value="old('board_roll_prefix', $exam?->board_roll_prefix)" />
        <x-ui.input name="board_center_code" :label="__('ui.board_center_code')" :value="old('board_center_code', $boardMeta['center_code'] ?? '')" />
        <x-ui.input name="board_session" :label="__('ui.board_session')" :value="old('board_session', $boardMeta['session'] ?? '')" />
    </div>
</x-ui.form-section>
