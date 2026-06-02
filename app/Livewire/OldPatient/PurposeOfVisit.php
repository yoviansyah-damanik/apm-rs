<?php

namespace App\Livewire\OldPatient;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class PurposeOfVisit extends Component
{
    /**
     * Summary of type
     * @var PurposeOfVisit
     * Type digunakan untuk menentukan jenis pelayanan
     * RujukPertama, Kontrol, KontrolPostRanap, RujukInternal
     */
    #[Reactive]
    public $purposeOfVisits = [];

    public function render()
    {
        return view('livewire.old-patient.purpose-of-visit');
    }

    public function placeholder()
    {
        return view('placeholders.purposeOfVisit');
    }

    public function setPurposeOfVisit(string $purposeOfVisit): void
    {
        $this->dispatch('setPurposeOfVisit', $purposeOfVisit);
        $this->dispatch('setFormStep');
    }
}
