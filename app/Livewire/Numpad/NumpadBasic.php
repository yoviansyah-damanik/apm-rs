<?php

namespace App\Livewire\Numpad;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;

class NumpadBasic extends Component
{
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

    protected $listeners = ['clearNumpad'];

    // Mapping jumlah digit ke tipe
    const DIGIT_TYPES = [
        6 => 'RM',
        13 => 'BPJS',
        16 => 'NIK',
    ];

    public function mount(
        $value = '',
        $maxLength = null,
        $placeholder = '',
        $label = '',
        $name = 'numpad',
        $isInvalid = false,
        $autoDetect = false,
    ) {
        $this->autoDetect = $autoDetect;
    }

    public function render()
    {
        return view('livewire.numpad.numpad-basic');
    }

    public function clearNumpad()
    {
        $this->value = '';
    }

    public function updatedValue($value)
    {
        // Auto detect tipe berdasarkan jumlah digit
        if ($this->autoDetect) {
            $length = strlen($value);
            $this->detectedType = self::DIGIT_TYPES[$length] ?? '';

            // Auto trigger enter jika sudah sesuai dengan salah satu tipe
            if (isset(self::DIGIT_TYPES[$length])) {
                $this->enter();
            }
        }

        $this->dispatch($this->updatedTrigger, [
            'name' => $this->name,
            'value' => $value
        ]);
    }

    public function getDetectedTypeLabel()
    {
        if (!$this->autoDetect || !$this->detectedType) {
            return '';
        }

        return match($this->detectedType) {
            'RM' => 'Rekam Medis',
            'BPJS' => 'No. BPJS',
            'NIK' => 'NIK',
            default => '',
        };
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
