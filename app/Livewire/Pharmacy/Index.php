<?php

namespace App\Livewire\Pharmacy;

use Livewire\Component;
use Livewire\Attributes\On;

class Index extends Component
{
    public $registerData;

    public $currentStep = 1;

    public function mount()
    {
        $this->dispatch('speak', text: 'Selamat datang di Antrean Farmasi. Silahkan masukkan kode booking anda untuk melanjutkan.');
    }

    public function render()
    {
        return view('livewire.pharmacy.index')
            ->layout('components.layouts.console-box');
    }

    #[On('setStep')]
    public function setStep($step = null)
    {
        if ($step === null)
            $this->currentStep += 1;
        else
            $this->currentStep = $step;
    }

    #[On('setRegisterData')]
    public function setRegisterData($data)
    {
        $this->registerData = $data;
    }
}
