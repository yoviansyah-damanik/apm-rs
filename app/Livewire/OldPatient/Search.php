<?php

namespace App\Livewire\OldPatient;

use App\Helpers\SettingHelper;
use Flux\Flux;
use App\Models\Patient;
use App\Models\Register;
use Livewire\Component;
use Livewire\Attributes\On;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Search extends Component
{
    public $numberStatus = 'rm';
    // Valid Prod
    // public $idNumber = '0002607785853';
    // public $idNumber = '';
    // Valid Dev
    public $idNumber = '';
    // public $idNumber = '';
    // public $idNumber = '046616';
    // public $idNumber = '999999';

    public $participantData;
    public bool $isLoading = false;
    public Patient|null $patient;
    public $isInvalid = false;
    public bool $showProcessModal = false;

    public function render()
    {
        return view('livewire.old-patient.search');
    }

    #[On('numpad-updated')]
    public function checkNumber($payload)
    {
        $this->idNumber = $payload['value'];
    }

    #[On('numpad-entered')]
    public function checkPatient($payload = null): void
    {
        // Jika payload dikirim, gunakan nilai dari payload
        if ($payload && isset($payload['value'])) {
            $this->idNumber = $payload['value'];
        }

        $this->reset('patient');

        // Validasi format nomor identitas (proses cepat)
        if ($this->numberStatus == '' || !in_array(strlen($this->idNumber), [6, 13, 16])) {
            LivewireAlert::title("Format nomor identitas salah")
                ->text("Format nomor identitas yang anda masukkan salah. 6 digit untuk Nomor Rekam Medis, 13 digit untuk nomor kartu BPJS, atau 16 digit untuk NIK.")
                ->withConfirmButton()
                ->confirmButtonText('OK')
                ->timer(0)
                ->warning()
                ->show();
            return;
        }

        // Tampilkan modal loading dan trigger lazy load
        $this->showProcessModal = true;
        Flux::modal('processPatient')->show();
    }

    /**
     * Method untuk proses pencarian patient dan cek registrasi (dipanggil lazy load)
     */
    public function processPatientCheck()
    {
        try {
            // Cari patient di database
            $this->patient = Patient::where('no_rkm_medis', $this->idNumber)
                ->orWhere('no_peserta', $this->idNumber)
                ->orWhere('no_ktp', $this->idNumber)
                ->first();

            if (!$this->patient) {
                $this->dispatch('patientCheckFailed', [
                    'type' => 'not_found',
                    'message' => "Silahkan periksa kembali nomor identitas anda dan masukkan nomor identitas anda dengan benar. Jika anda pasien baru, silahkan pilih menu Antrean Loket untuk pendaftaran pasien baru."
                ]);
                return;
            }

            // Cek registrasi hari ini
            $excludePolys = SettingHelper::getExcludePolyclinics();
            $registered = Register::where('no_rkm_medis', $this->patient->no_rkm_medis)
                ->where('tgl_registrasi', now()->format('Y-m-d'))
                ->where('stts', '<>', 'Batal')
                ->where('status_lanjut', 'Ralan')
                ->whereNotIn('kd_poli', $excludePolys)
                ->first();

            // Tutup modal loading
            $this->showProcessModal = false;
            Flux::modal('processPatient')->close();

            // Cek Apakah Sudah Melakukan Pendaftaran
            if ($registered) {
                $this->dispatch('setRegistration', $registered);
                $this->dispatch('setStep', 2);
                $this->dispatch('setFormStep', 8);
            } else {
                Flux::modal('participantData')->show();
                $this->dispatch('setPatient', $this->patient);
                $this->dispatch('participantCheck');
            }
        } catch (\Exception $e) {
            $this->dispatch('patientCheckFailed', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Handle ketika pengecekan patient gagal
     */
    #[On('patientCheckFailed')]
    public function handlePatientCheckFailed($data)
    {
        $this->showProcessModal = false;
        Flux::modal('processPatient')->close();

        if ($data['type'] === 'not_found') {
            LivewireAlert::title("Pasien tidak ditemukan")
                ->text($data['message'])
                ->withConfirmButton()
                ->withCancelButton()
                ->confirmButtonText('Ambil Antrean')
                ->onConfirm('redirectToAmbilAntrean')
                ->timer(0)
                ->warning()
                ->show();
        } else {
            LivewireAlert::title("Terjadi Kesalahan")
                ->text($data['message'])
                ->withConfirmButton()
                ->confirmButtonText('OK')
                ->timer(0)
                ->error()
                ->show();
        }
    }

    public function redirectToAmbilAntrean()
    {
        return $this->redirectroute('new-patient', navigate: true);
    }

    public function nextStep()
    {
        $this->isLoading = true;
        $this->dispatch('setStep', 2);
    }

    #[On('updatePhoneNumber')]
    public function updatePhoneNumber($newPhoneNumber)
    {
        $this->patient->update(['no_tlp' => $newPhoneNumber]);
    }
}
