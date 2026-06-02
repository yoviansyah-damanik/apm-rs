<?php

namespace App\Livewire\Numpad;

use Livewire\Component;

class NumpadSukon extends Component
{
    public $value = '';
    public $maxLength = 25; // Maksimal panjang nomor surat kontrol BPJS
    public $placeholder = '';
    public $disabled = false;
    public $label = '';
    public $name = 'numpad';
    public $type = 'rm';

    protected $listeners = ['clearNumpad'];

    public function mount($value = '', $placeholder = '', $disabled = false, $label = '', $name = 'numpad', $type = 'rm')
    {
        $this->value = $value;
        $this->placeholder = $placeholder;
        $this->disabled = $disabled;
        $this->label = $label;
        $this->name = $name;
        $this->type = $type;
    }

    public function clearNumpad()
    {
        $this->value = '';
    }

    public function updatedValue($value)
    {
        $this->dispatch('numpad-updated', [
            'name' => $this->name,
            'value' => $value
        ]);
    }

    public function enter()
    {
        if ($this->disabled) {
            return;
        }

        $this->dispatch('numpad-enter', true);
    }

    public function render()
    {
        return view('livewire.numpad.numpad-sukon');
    }
}
