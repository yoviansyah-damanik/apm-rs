<?php

namespace App\Livewire\CheckIn;

use Flux\Flux;
use App\Models\Sep;
use App\Models\JknRef;
use App\Models\Patient;
use Livewire\Component;
use App\Models\Register;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Services\BpjsService;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Index extends Component
{
    public $idNumber; // Akan diisi otomatis dengan format BYYYYMMDD oleh komponen
    public $patient = null;
    public $jknBooking = null;
    public $participantData = [];
    public int $currentStep = 1; // 1: Search, 2: Process
    public int $formStep = 1; // 1: Biometric, 2: Elegtability, 3. Finish (Check In)
    public string $formTitle;
    public string $formSubtitle;
    public string $defaultBpjsPayType;

    public function mount()
    {
        $this->defaultBpjsPayType = env('DEFAULT_BPJS_PAY_TYPE', '');

        $this->setFormStep($this->formStep);
        $this->setFormTitle($this->formStep);

        $this->dispatch('speak', text: 'Selamat datang di Check In Mandiri. Silahkan masukkan nomor kode booking anda, atau silahkan pilih via antrean mobile JKN.');
    }

    public function render()
    {
        return view('livewire.check-in.index')
            ->layout('components.layouts.console-box');
    }

    /**
     * Set step untuk navigasi
     */
    #[On('setStep')]
    public function setStep($step)
    {
        $this->currentStep = $step;

        if ($this->currentStep == 2) {
            $this->formStep = 1;
        }
        $this->setFormTitle($this->formStep);

        // if ($this->currentStep == 2 && in_array($this->formStep, [1, 2])) {
        //     $existSep = Sep::where('no_rawat', $this->jknBooking['no_rawat'])->exists();
        //     if ($existSep) {
        //         $this->formStep = 3;
        //     } else {
        //         $this->formStep = 1;
        //     }
        //     $this->setFormTitle($this->formStep);
        // }
    }

    /**
     * Kembali ke step sebelumnya
     */
    #[On('prevStep')]

    public function prevStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        } else {
            // Reset semua data
            $this->reset(['patient', 'jknBooking', 'participantData', 'idNumber']);
            $this->currentStep = 1;
        }
    }

    #[On('setFormStep')]

    public function setFormStep($step = null): void
    {
        if ($step == null) {
            $this->formStep += 1;
        } else {
            $this->formStep = $step;
        }

        if ($this->currentStep == 2 && $this->formStep == 3) {
            $existSep = Sep::where('no_rawat', $this->jknBooking['no_rawat'])->exists();
            if (!$existSep) {
                $this->prevFormStep();
            }
        }

        if ($this->formStep + 1 > 3) {
            $this->formStep = 3;
        }

        $this->setFormTitle($this->formStep);
    }

    #[On('prevFormStep')]
    public function prevFormStep(): void
    {
        if ($this->formStep - 1 < 1) {
            $this->setStep(1);
            $this->formStep = 1;
        } else
            $this->formStep -= 1;

        if ($this->currentStep == 2 && in_array($this->formStep, [1, 2])) {
            $existSep = Sep::where('no_rawat', $this->jknBooking['no_rawat'])->exists();
            if ($existSep) {
                $this->setFormStep(3);
            }
        }

        $this->setFormStep($this->formStep);
        $this->setFormTitle($this->formStep);
    }

    public function setFormTitle(int $step)
    {
        switch ($step) {
            case 1:
                $this->formTitle = "Biometrik";
                $this->formSubtitle = "Silahkan lakukan validasi biometrik terlebih dahulu. Anda hanya perlu melakukan salah satu validasi biometrik.";
                break;
            case 2:
                $this->formTitle = "Elegtabilitas Peserta";
                $this->formSubtitle = "Silahkan konfirmasi Elegtabilitas Peserta BPJS anda.";
                break;
            case 3:
                $this->formTitle = "Status Check In";
                $this->formSubtitle = "Silahkan lakukan Check In Mandiri.";
                break;
            case 4:
                $this->formTitle = "Selesai Check In";
                $this->formSubtitle = "Anda selesai melakukan Check In mandiri.";
                break;
        }

        if ($this->currentStep === 2) {
            $this->dispatch('speak', text: "$this->formTitle. $this->formSubtitle");
        }
    }

    #[On('setParticipantData')]
    public function setParticipantData(array $participantData)
    {
        $this->participantData = $participantData;
    }

    #[On('setJknBooking')]
    public function setJknBooking(JknRef $jknBooking)
    {
        $this->jknBooking = $jknBooking;
    }

    #[On('setPatient')]
    public function setPatient(array $patient)
    {
        $this->patient = $patient;
    }
}
