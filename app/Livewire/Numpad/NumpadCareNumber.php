<?php

namespace App\Livewire\Numpad;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Attributes\Modelable;

class NumpadCareNumber extends Component
{
    #[Modelable]
    public $value = '';
    public $maxLength = null;
    public $placeholder = '';

    #[Reactive]
    public $disabled;
    public $label = '';
    public $enteredTrigger = 'numpad-entered';
    public $updatedTrigger = 'numpad-updated';
    public $name = 'numpad';
    #[Reactive]
    public $isInvalid = false;
    public $autoDetect = false; // Auto detect tipe berdasarkan jumlah digit
    public $detectedType = ''; // RM, BPJS, NIK
    public $careCode = '0'; // Kode Rawatan default

    protected $listeners = ['clearNumpad'];

    public function mount(
        $value = '',
        $maxLength = 15,
        $autoDetect = false,
        $careCode = 'B',
    ) {
        $this->autoDetect = $autoDetect;
        $this->careCode = $careCode;

        // Set default value jika kosong: BYYYYMMDD format
        if (empty($value)) {
            $this->value = $this->careCode . now()->format('Ymd');
        } else {
            $this->value = $value;
        }
    }

    public function render()
    {
        return view('livewire.numpad.numpad-care-number');
    }

    public function clearNumpad()
    {
        // Reset ke format default: BYYYYMMDD
        $this->value = $this->careCode . now()->format('Ymd');
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

        // Kirim nilai $value ke fungsi yang dipanggil
        $this->dispatch($this->enteredTrigger, [
            'name' => $this->name,
            'value' => $this->value
        ]);
    }

    #[On('buttonEnabledStatus')]
    public function buttonEnabledStatus($status)
    {
        $this->disabled = $status;
    }

    public function updateBarcode(string $value)
    {
        $this->value = $value;

        $this->dispatch($this->updatedTrigger, [
            'name' => $this->name,
            'value' => $this->value
        ]);
    }
}
