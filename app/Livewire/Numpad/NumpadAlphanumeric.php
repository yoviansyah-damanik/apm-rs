<?php

namespace App\Livewire\Numpad;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;

class NumpadAlphanumeric extends Component
{
    public $value = '';
    public $maxLength = 15;
    public $placeholder = '';

    #[Reactive]
    public $disabled;
    public $label = '';
    public $enteredTrigger = 'numpad-entered';
    public $updatedTrigger = 'numpad-updated';
    public $name = 'numpad';
    #[Reactive]
    public $isInvalid = false;

    protected $listeners = ['clearNumpad'];

    public function mount(
        $value = '',
        $maxLength = 15,
        $placeholder = '',
        $label = '',
        $name = 'numpad',
        $isInvalid = false,
    ) {
        $this->value = $value;
        $this->maxLength = $maxLength;
        $this->placeholder = $placeholder;
        $this->label = $label;
        $this->name = $name;
        $this->isInvalid = $isInvalid;
    }

    public function render()
    {
        return view('livewire.numpad.numpad-alphanumeric');
    }

    public function clearNumpad()
    {
        $this->value = '';
    }

    public function updatedValue($value)
    {
        $this->dispatch($this->updatedTrigger, [
            'name' => $this->name,
            'value' => $value
        ]);
    }

    public function enter()
    {
        if ($this->disabled) {
            return;
        }

        $this->dispatch($this->enteredTrigger, [
            'name' => $this->name,
            'value' => $this->value
        ]);
    }

    public function updateBarcode(string $value)
    {
        $this->value = $value;

        $this->dispatch($this->updatedTrigger, [
            'name'  => $this->name,
            'value' => $this->value,
        ]);
    }

    #[On('buttonEnabledStatus')]
    public function buttonEnabledStatus($status)
    {
        $this->disabled = $status;
    }
}
