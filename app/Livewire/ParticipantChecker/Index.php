<?php

namespace App\Livewire\ParticipantChecker;

use Livewire\Component;
use Livewire\Attributes\On;

class Index extends Component
{
    public $participantData;

    public $currentStep = 1;

    public function mount()
    {
        $this->dispatch('speak', text: 'Selamat datang di Cek Kepesertaan BPJS. Silahkan masukkan nomor rekam medis, nomor induk kependudukan, atau nomor kartu BPJS anda untuk melihat informasi kepesertaan.');
    }

    public function render()
    {
        return view('livewire.participant-checker.index')
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

    #[On('setParticipantData')]
    public function setParticipantData($data)
    {
        $this->participantData = $data;
    }
}
