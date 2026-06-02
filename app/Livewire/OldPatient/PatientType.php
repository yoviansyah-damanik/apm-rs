<?php

namespace App\Livewire\OldPatient;

use App\Enums\PurposeOfVisit;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class PatientType extends Component
{
    #[Reactive]
    public $patient;
    #[Reactive]
    public array $participantData;
    #[Reactive]
    public $payTypes;
    #[Reactive]
    public bool $canUseBpjs;
    #[Reactive]
    public array $defaultBpjsPayTypes;

    public function mount()
    {
        // dd($this->payTypes);
    }

    public function render()
    {
        return view('livewire.old-patient.patient-type');
    }

    public function placeholder()
    {
        return view('placeholders.patientType');
    }

    public function setPayType($type): void
    {
        if (!in_array($type, $this->payTypes->pluck('kd_pj')->toArray())) {
            LivewireAlert::title('Terjadi kesalahan')
                ->text('Jenis pasien yang anda pilih tidak sesuai.')
                ->warning()
                ->show();
            return;
        }

        // Validasi: Cegah user memilih BPJS jika canUseBpjs = false
        if (in_array($type, $this->defaultBpjsPayTypes) && !$this->canUseBpjs) {
            LivewireAlert::title('BPJS tidak dapat digunakan!')
                ->text('Terdapat masalah pada data kepesertaan atau koneksi. Silahkan pilih jenis pasien lain.')
                ->warning()
                ->show();
            return;
        }

        // Ambil data lengkap dari payTypes collection
        $payTypeData = $this->payTypes->firstWhere('kd_pj', $type);

        // Set payType terlebih dahulu sebelum setFormStep
        // agar logic di setFormStep bisa check jenis pasien dengan benar
        $this->dispatch('setPayType', [
            'kd_pj' => $type,
            'png_jawab' => $payTypeData?->png_jawab ?? $type
        ]);

        // Set purposeOfVisit default hanya untuk BPJS
        if (!in_array($type, $this->defaultBpjsPayTypes)) {
            $this->dispatch('setPurposeOfVisit', PurposeOfVisit::Kontrol->value);
        }

        // Pindah ke step 2 (Purpose of Visit untuk BPJS, atau akan di-skip ke Schedule untuk non-BPJS)
        $this->dispatch('setFormStep', 2);
    }
}
