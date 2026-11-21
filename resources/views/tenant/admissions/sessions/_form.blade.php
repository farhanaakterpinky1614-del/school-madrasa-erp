@php($session = $session ?? null)
<x-ui.form-section :title="__('ui.nav.admission_sessions')">
<div class="grid gap-4 sm:grid-cols-2">
<x-ui.input name="name" :label="__('ui.col_name')" :value="old('name', $session->name ?? '')" required />
<x-ui.input name="code" :label="__('ui.code')" :value="old('code', $session->code ?? '')" />
<x-ui.input name="public_slug" :label="__('ui.public_slug')" :value="old('public_slug', $session->public_slug ?? '')" />
<x-ui.select name="academic_year_id" :label="__('ui.nav.years')" required>
@foreach($years as $y)<option value="{{ $y->id }}" @selected(old('academic_year_id', $session->academic_year_id ?? null)==$y->id || (!$session && $y->is_current))>{{ $y->name }}</option>@endforeach
</x-ui.select>
<x-ui.select name="school_class_id" :label="__('ui.nav.classes')">
<option value="">—</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(old('school_class_id', $session->school_class_id ?? null)==$c->id)>{{ $c->name }}</option>@endforeach
</x-ui.select>
<x-ui.select name="admission_form_template_id" :label="__('ui.nav.admission_forms')">
<option value="">—</option>
@foreach($templates as $t)<option value="{{ $t->id }}" @selected(old('admission_form_template_id', $session->admission_form_template_id ?? null)==$t->id)>{{ $t->name }}</option>@endforeach
</x-ui.select>
<x-ui.input name="opens_at" type="datetime-local" :label="__('ui.opens_at')" :value="old('opens_at', isset($session) && $session->opens_at ? $session->opens_at->format('Y-m-d\\TH:i') : '')" />
<x-ui.input name="closes_at" type="datetime-local" :label="__('ui.closes_at')" :value="old('closes_at', isset($session) && $session->closes_at ? $session->closes_at->format('Y-m-d\\TH:i') : '')" />
<x-ui.input name="capacity" type="number" :label="__('ui.capacity')" :value="old('capacity', $session->capacity ?? '')" />
</div>
<label class="mt-4 flex items-center gap-2 text-sm"><input type="checkbox" name="allow_online" value="1" @checked(old('allow_online', $session->allow_online ?? true))> {{ __('ui.allow_online') }}</label>
<label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="allow_offline" value="1" @checked(old('allow_offline', $session->allow_offline ?? true))> {{ __('ui.allow_offline') }}</label>
<label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $session->is_active ?? true))> {{ __('ui.status_active') }}</label>
<div class="mt-6"><x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button></div>
</x-ui.form-section>
