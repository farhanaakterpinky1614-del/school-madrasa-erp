<div>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ __('ui.components_title') }}</h1>
            <p class="mt-1 text-sm text-muted">{{ __('ui.components_subtitle') }}</p>
        </div>
        <x-ui.button type="button" @click="$dispatch('open-modal', 'confirm-demo')">
            <x-lucide-message-square class="h-4 w-4" />
            {{ __('ui.open_modal') }}
        </x-ui.button>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.form-section :title="__('ui.form_demo')" :description="__('ui.form_demo_help')">
            <form wire:submit="save" class="space-y-4">
                <x-ui.input
                    wire:model="name"
                    :label="__('ui.field_name')"
                    :error="$errors->first('name')"
                    id="demo-name"
                />
                <x-ui.input
                    type="email"
                    wire:model="email"
                    :label="__('ui.field_email')"
                    :error="$errors->first('email')"
                    id="demo-email"
                />
                <x-ui.select wire:model="status" :label="__('ui.field_status')" id="demo-status">
                    <option value="active">{{ __('ui.status_active') }}</option>
                    <option value="pending">{{ __('ui.status_pending') }}</option>
                    <option value="archived">{{ __('ui.status_archived') }}</option>
                </x-ui.select>
                <x-ui.textarea wire:model="notes" :label="__('ui.field_notes')" id="demo-notes" />

                <div class="flex flex-wrap gap-2 pt-2">
                    <x-ui.button type="submit">{{ __('ui.save') }}</x-ui.button>
                    <x-ui.button type="button" variant="outline">{{ __('ui.cancel') }}</x-ui.button>
                    <x-ui.button type="button" variant="danger">{{ __('ui.delete') }}</x-ui.button>
                </div>
            </form>
        </x-ui.form-section>

        <div class="space-y-6">
            <x-ui.form-section :title="__('ui.badges_title')">
                <div class="flex flex-wrap gap-2">
                    <x-ui.badge tone="primary">Primary</x-ui.badge>
                    <x-ui.badge tone="success">{{ __('ui.status_active') }}</x-ui.badge>
                    <x-ui.badge tone="warning">{{ __('ui.status_pending') }}</x-ui.badge>
                    <x-ui.badge tone="danger">{{ __('ui.status_archived') }}</x-ui.badge>
                    <x-ui.badge>Slate</x-ui.badge>
                </div>
            </x-ui.form-section>

            <x-ui.table :headers="[__('ui.col_name'), __('ui.col_status'), __('ui.col_action')]">
                <tr>
                    <td class="px-4 py-3">Ayesha Rahman</td>
                    <td class="px-4 py-3"><x-ui.badge tone="success">{{ __('ui.status_active') }}</x-ui.badge></td>
                    <td class="px-4 py-3">
                        <x-ui.button size="sm" variant="ghost" type="button">{{ __('ui.view') }}</x-ui.button>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 py-3">মোহাম্মদ করিম</td>
                    <td class="px-4 py-3"><x-ui.badge tone="warning">{{ __('ui.status_pending') }}</x-ui.badge></td>
                    <td class="px-4 py-3">
                        <x-ui.button size="sm" variant="ghost" type="button">{{ __('ui.view') }}</x-ui.button>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 py-3">عبد الله</td>
                    <td class="px-4 py-3"><x-ui.badge tone="primary">Primary</x-ui.badge></td>
                    <td class="px-4 py-3">
                        <x-ui.button size="sm" variant="ghost" type="button">{{ __('ui.view') }}</x-ui.button>
                    </td>
                </tr>
            </x-ui.table>
        </div>
    </div>

    <x-ui.modal name="confirm-demo" :title="__('ui.modal_title')">
        <p class="text-sm text-muted">{{ __('ui.modal_body') }}</p>
        <x-slot:footer>
            <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', 'confirm-demo')">
                {{ __('ui.cancel') }}
            </x-ui.button>
            <x-ui.button type="button" @click="$dispatch('close-modal', 'confirm-demo')">
                {{ __('ui.confirm') }}
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
