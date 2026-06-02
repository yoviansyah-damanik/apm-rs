<?php

namespace App\Livewire\OldPatient;

use Livewire\Component;
use App\Services\RegisterService;
use Livewire\Attributes\Reactive;

class Confirmation extends Component
{
    #[Reactive]
    public $patient;
    #[Reactive]
    public $schedule;
    #[Reactive]
    public $purposeOfVisit;
    #[Reactive]
    public $payType;
    #[Reactive]
    public $participantData;

    public function render()
    {
        return view('livewire.old-patient.confirmation');
    }

    public function placeholder()
    {
        return view('placeholders.confirmation');
    }

    public function register()
    {
        $registerService = new RegisterService();

        // Ambil data dari schedule
        $polyclinicId = $this->schedule['kd_poli'] ?? null;
        $polyclinicName = $this->schedule['nm_poli'] ?? null;
        $doctorId = $this->schedule['doctor']['kd_dokter'] ?? null;
        $doctorName = $this->schedule['doctor']['nm_dokter'] ?? null;

        $register = $registerService->insert(
            $this->patient,
            null,
            [
                'polyclinicId' => $polyclinicId,
                'polyclinicName' => $polyclinicName,
            ],
            [
                'doctorId' => $doctorId,
                'doctorName' => $doctorName,
            ],
            $this->payType,
            false,
        );

        if ($register) {
            // Dispatch event untuk pindah ke step cetak antrean
            $this->dispatch('setRegistration', $register);
            $this->dispatch('setFormStep', 8);
        }
    }
}
