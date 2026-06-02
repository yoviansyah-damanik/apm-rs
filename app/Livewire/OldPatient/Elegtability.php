<?php

namespace App\Livewire\OldPatient;

use Livewire\Component;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;

class Elegtability extends Component
{
    #[Reactive]
    public $reference;
    #[Reactive]
    public $controlLetter;
    #[Reactive]
    public array $participantData;
    #[Reactive]
    public $patient;
    #[Reactive]
    public $schedule;
    #[Reactive]
    public $purposeOfVisit;
    #[Reactive]
    public $payType;
    #[Reactive]
    public $defaultBpjsPayType;

    public function render(): View
    {
        return view('livewire.old-patient.elegtability');
    }

    #[On('elegtabilityData')]
    public function elegtabilityData($payload)
    {
        if ($payload['status'] === true) {
            $this->dispatch('setRegistration', $payload['registration']);
            $this->dispatch('setFormStep', 8);
        }
    }
}
