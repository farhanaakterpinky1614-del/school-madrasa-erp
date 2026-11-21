@php $s = $structure ?? null; @endphp
<div class="grid gap-4 sm:grid-cols-2">
    <x-ui.input name="name" :label="__('ui.col_name')" :value="old('name', $s?->name)" required />
    <x-ui.input name="code" :label="__('ui.code')" :value="old('code', $s?->code)" />
    <x-ui.input name="basic_salary" type="number" step="0.01" min="0" :label="__('ui.basic_salary')" :value="old('basic_salary', $s?->basic_salary ?? 0)" required />
    <x-ui.input name="house_rent" type="number" step="0.01" min="0" :label="__('ui.house_rent')" :value="old('house_rent', $s?->house_rent ?? 0)" />
    <x-ui.input name="medical" type="number" step="0.01" min="0" :label="__('ui.medical')" :value="old('medical', $s?->medical ?? 0)" />
    <x-ui.input name="conveyance" type="number" step="0.01" min="0" :label="__('ui.conveyance')" :value="old('conveyance', $s?->conveyance ?? 0)" />
    <x-ui.input name="other_allowance" type="number" step="0.01" min="0" :label="__('ui.other_allowance')" :value="old('other_allowance', $s?->other_allowance ?? 0)" />
    <x-ui.input name="deductions" type="number" step="0.01" min="0" :label="__('ui.deductions')" :value="old('deductions', $s?->deductions ?? 0)" />
</div>
<x-ui.textarea name="notes" :label="__('ui.notes')" :value="old('notes', $s?->notes)" />
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $s?->is_active ?? true)) /> {{ __('ui.staff_status_active') }}</label>
