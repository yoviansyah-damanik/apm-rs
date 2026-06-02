<?php

namespace App\Livewire\CheckIn;

use Livewire\Component;
use Livewire\Attributes\On;

class Biometric extends Component
{
    public $participantData;

    public function render()
    {
        return view('livewire.check-in.biometric');
    }

    #[On('biometricStatus')]
    public function biometricStatus($status)
    {
        if ($status === true)
            $this->dispatch('setFormStep');
    }
}
