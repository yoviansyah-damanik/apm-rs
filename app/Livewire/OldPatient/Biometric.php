<?php

namespace App\Livewire\OldPatient;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;

class Biometric extends Component
{
    #[Reactive]
    public array $participantData;

    public function mount()
    {
        $this->dispatch('setStep', 6);
    }

    public function render()
    {
        return view('livewire.old-patient.biometric');
    }

    #[On('biometricStatus')]
    public function biometricStatus($status)
    {
        if ($status === true)
            $this->dispatch('setFormStep');
    }
}
