<?php

namespace App\Livewire\Demo;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('UI kit')]
class UiKitchenSink extends Component
{
    public string $name = '';

    public string $email = '';

    public string $notes = '';

    public string $status = 'active';

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email'],
            'status' => ['required', 'in:active,pending,archived'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        session()->flash('status', __('ui.demo_saved'));
    }

    public function render()
    {
        return view('livewire.demo.ui-kitchen-sink');
    }
}
