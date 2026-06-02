<?php

namespace App\Livewire\OldPatient;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;

class Schedule extends Component
{
    #[Reactive]
    public $reference;
    #[Reactive]
    public $controlLetter;
    public $schedules;
    public $onlyPoly = null;

    public function mount()
    {
        $this->onlyPoly = null;
        if ($this->controlLetter) {
            $this->onlyPoly = $this->controlLetter['poliTujuan'];
        } else {
            if ($this->reference) {
                $this->onlyPoly = $this->reference['poliRujukan']['kode'];
            }
        }
    }

    public function render()
    {
        return view('livewire.old-patient.schedule');
    }

    public function placeholder()
    {
        return view('placeholders.schedules');
    }

    public function setSchedule(string $_id): void
    {
        $schedule = $this->schedules
            ->where('_id', $_id)
            ->first();

        $this->dispatch('setSchedule', [
            ...$schedule
        ]);
        $this->dispatch('setFormStep');
    }
}
