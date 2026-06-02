<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Reactive;

class BpjsParticipantData extends Component
{
    #[Reactive]
    public $participantData;

    // public function mount(array $participantData)
    // {
    //     $this->participantData = $participantData;
    // }

    public function render()
    {
        return view('livewire.bpjs-participant-data');
    }
}
