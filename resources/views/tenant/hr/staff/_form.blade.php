@php $s = $staff ?? null; @endphp
<div class="grid gap-4 sm:grid-cols-2">
    <x-ui.input name="first_name" :label="__('ui.first_name')" :value="old('first_name', $s?->first_name)" required />
    <x-ui.input name="last_name" :label="__('ui.last_name')" :value="old('last_name', $s?->last_name)" />
    <x-ui.input name="name_bn" :label="__('ui.name_bn')" :value="old('name_bn', $s?->name_bn)" />
    <x-ui.select name="gender" :label="__('ui.gender')">
        <option value="">—</option>
        @foreach (['male','female','other'] as $g)
            <option value="{{ $g }}" @selected(old('gender', $s?->gender) === $g)>{{ ucfirst($g) }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.input name="phone" :label="__('ui.phone')" :value="old('phone', $s?->phone)" />
    <x-ui.input name="email" type="email" :label="__('ui.email')" :value="old('email', $s?->email)" />
    <x-ui.input name="nid_no" :label="__('ui.nid')" :value="old('nid_no', $s?->nid_no)" />
    <x-ui.input name="joined_on" type="date" :label="__('ui.joined_on')" :value="old('joined_on', optional($s?->joined_on)->format('Y-m-d'))" />
    <x-ui.select name="staff_type" :label="__('ui.staff_type')" required>
        @foreach (['employee','teacher','both'] as $t)
            <option value="{{ $t }}" @selected(old('staff_type', $s?->staff_type ?? 'employee') === $t)>{{ __('ui.staff_type_'.$t) }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="status" :label="__('ui.col_status')">
        @foreach (['active','inactive','terminated'] as $st)
            <option value="{{ $st }}" @selected(old('status', $s?->status ?? 'active') === $st)>{{ __('ui.staff_status_'.$st) }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="designation_id" :label="__('ui.designation')">
        <option value="">—</option>
        @foreach ($designations as $d)
            <option value="{{ $d->id }}" @selected((int) old('designation_id', $s?->designation_id) === $d->id)>{{ $d->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="department_id" :label="__('ui.department')">
        <option value="">—</option>
        @foreach ($departments as $d)
            <option value="{{ $d->id }}" @selected((int) old('department_id', $s?->department_id) === $d->id)>{{ $d->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.select name="branch_id" :label="__('ui.nav.branches')">
        <option value="">—</option>
        @foreach ($branches as $b)
            <option value="{{ $b->id }}" @selected((int) old('branch_id', $s?->branch_id) === $b->id)>{{ $b->name }}</option>
        @endforeach
    </x-ui.select>
    <x-ui.input name="city" :label="__('ui.city')" :value="old('city', $s?->city)" />
    <x-ui.input name="division" :label="__('ui.division')" :value="old('division', $s?->division)" />
    <x-ui.input name="bank_name" :label="__('ui.bank_name')" :value="old('bank_name', $s?->bank_name)" />
    <x-ui.input name="bank_account" :label="__('ui.bank_account')" :value="old('bank_account', $s?->bank_account)" />
</div>
<x-ui.textarea name="address" :label="__('ui.address')" :value="old('address', $s?->address)" />
<x-ui.textarea name="notes" :label="__('ui.notes')" :value="old('notes', $s?->notes)" />
