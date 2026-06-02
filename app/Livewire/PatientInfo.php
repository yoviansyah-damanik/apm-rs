<?php

namespace App\Livewire;

use App\Helpers\MagicHelper;
use Flux\Flux;
use App\Models\Patient;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\BpjsService;
use Livewire\Attributes\Reactive;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class PatientInfo extends Component
{
    #[Reactive]
    public $patient;
    public $participantData;
    public $newPhoneNumber = '';
    public bool $isInvalid = true;
    public bool $isMRSame = false;
    public bool $hasError = false;
    public string $errorMessage = '';
    public int $errorCode = 0;
    // Flag untuk menandai apakah BPJS dapat digunakan (false jika error atau kode 201)
    public bool $canUseBpjs = true;

    public bool $isNewPatientButton = true;

    public string $phoneNumberExp = '/^0(?!0{10,12}$)[0-9]{10,12}$/';

    public function render()
    {
        return view('livewire.patient-info');
    }

    public function updatePatientData()
    {
        try {
            if (!is_null($this->participantData['peserta']['mr']['noMR']) && ($this->participantData['peserta']['mr']['noMR'] != $this->patient['no_rkm_medis'])) {
                Flux::modal('participantCheck')->close();
                LivewireAlert::title("Gagal")
                    ->text('Nomor rekam medis yang terdata pada kami tidak sama dengan yang terdata pada BPJS. Silahkan melakukan perubahan data di Loket Pendaftaran.')
                    ->error()
                    ->timer(0)
                    ->show();
                return;
            }

            $patient = Patient::when(
                strlen($this->patient['no_peserta']) == 13,
                function ($q) {
                    return $q->where('no_peserta', $this->patient['no_peserta']);
                },
                function ($q) {
                    return $q->where('no_ktp', $this->patient['no_ktp']);
                }
            )
                ->first();

            $patient->no_ktp = $this->participantData['peserta']['nik'];
            $patient->no_peserta = $this->participantData['peserta']['noKartu'];
            $patient->nm_pasien = $this->participantData['peserta']['nama'];
            $patient->tgl_lahir = $this->participantData['peserta']['tglLahir'];
            $patient->save();

            // Flux::modal('bpjsParticipantCheck')->close();
            $this->dispatch('setPatient', $patient);
            LivewireAlert::title("Sukses")
                ->text('Data Rekam Medis anda berhasil diperbarui.')
                ->success()
                ->timer(0)
                ->show();
        } catch (\Exception $e) {
            LivewireAlert::title("Terjadi kesalahan")
                ->text($e->getMessage())
                ->warning()
                ->timer(0)
                ->show();
        }
    }

    #[On('participantCheck')]
    public function participantCheck($participantData = null)
    {
        try {
            // Reset error state
            $this->hasError = false;
            $this->errorMessage = '';
            $this->errorCode = 0;
            $this->isMRSame = true;
            $this->canUseBpjs = true; // Reset flag BPJS

            $nama = $this->patient->nm_pasien;

            $identifier = strlen($this->patient->no_peserta) == 13
                ? $this->patient->no_peserta
                : $this->patient->no_ktp;

            if ($participantData == null) {
                $bpjsService = new BpjsService($identifier);
                $payload = $bpjsService->getParticipant();

                // Check if request was successful
                if (isset($payload['success']) && $payload['success'] === false) {
                    // Handle error response from BPJS
                    $this->errorCode = $payload['data']['metaData']['code'] ?? 500;
                    $this->errorMessage = $payload['data']['metaData']['message'] ?? 'Koneksi ke server BPJS gagal';

                    // Check if error code is 20X (200-299) - treat as success with warning
                    if ($this->errorCode >= 200 && $this->errorCode < 300) {
                        // Code 20X (bukan 200): Data ada tapi ada kondisi khusus
                        $this->hasError = false;
                        // Set flag BPJS tidak dapat digunakan untuk kode 201 atau error 20X lainnya
                        $this->canUseBpjs = false;

                        // Show warning alert untuk code 20X
                        // LivewireAlert::title("Perhatian")
                        //     ->text("Kode: {$this->errorCode} - {$this->errorMessage}")
                        //     ->warning()
                        //     ->timer(5000)
                        //     ->show();

                        $this->dispatch('speak', text: "Data ditemukan atas nama " . MagicHelper::format_patient_name($nama) . ". Perhatian. {$this->errorMessage}");
                    } else {
                        // Code selain 20X: Error sebenarnya
                        $this->hasError = true;
                        // Set flag BPJS tidak dapat digunakan jika ada error
                        $this->canUseBpjs = false;

                        LivewireAlert::title("Koneksi Gagal")
                            ->text("Kode Error: {$this->errorCode} - {$this->errorMessage}")
                            ->error()
                            ->timer(5000)
                            ->show();

                        $this->dispatch('speak', text: "Data ditemukan atas nama " . MagicHelper::format_patient_name($nama) . ". Koneksi ke server BPJS gagal.");
                    }

                    // Kirim flag canUseBpjs ke parent component
                    $this->dispatch('setCanUseBpjs', $this->canUseBpjs);

                    return;
                }

                // Check if response has valid data structure
                if (!isset($payload['data']['peserta'])) {
                    $this->hasError = true;
                    $this->errorCode = 500;
                    $this->errorMessage = 'Response dari BPJS tidak memiliki struktur data yang valid';
                    // Set flag BPJS tidak dapat digunakan jika data tidak valid
                    $this->canUseBpjs = false;

                    LivewireAlert::title("Data Tidak Valid")
                        ->text($this->errorMessage)
                        ->warning()
                        ->timer(3000)
                        ->show();

                    // Kirim flag canUseBpjs ke parent component
                    $this->dispatch('setCanUseBpjs', $this->canUseBpjs);
                    $this->dispatch('speak', text: "Data ditemukan atas nama " . MagicHelper::format_patient_name($nama) . ". Data kepesertaan BPJS tidak valid.");

                    return;
                }

                // SUCCESS - Process participant data
                $this->participantData = $payload['data'];
            } else {
                $this->participantData = $participantData;
            }

            $this->dispatch('setParticipantData', $this->participantData);

            // Cek status kepesertaan BPJS
            if (isset($this->participantData['peserta']['statusPeserta']['keterangan'])) {
                $statusBpjs = $this->participantData['peserta']['statusPeserta']['keterangan'];
                // Jika status bukan AKTIF, set canUseBpjs = false
                if ($statusBpjs === 'AKTIF') {
                    $this->dispatch('speak', text: "Data ditemukan atas nama " . MagicHelper::format_patient_name($nama) . ". Status kepesertaan BPJS aktif. BPJS Kesehatan dapat anda gunakan.");
                } else {
                    $this->canUseBpjs = false;
                    $this->dispatch('speak', text: "Data ditemukan atas nama " . MagicHelper::format_patient_name($nama) . ". Status kepesertaan BPJS tidak aktif. {$statusBpjs}. Anda hanya dapat mendaftar sebagai pasien umum.");
                }
            } else {
                $this->dispatch('speak', text: "Data ditemukan atas nama " . MagicHelper::format_patient_name($nama) . ". Anda tidak terdaftar BPJS Kesehatan. Anda hanya dapat mendaftar sebagai pasien umum.");
            }

            // PRIORITAS: Check if Medical Record number matches
            if (
                !is_null($this->participantData['peserta']['mr']['noMR']) &&
                $this->participantData['peserta']['mr']['noMR'] != $this->patient['no_rkm_medis']
            ) {
                $this->isMRSame = false;

                LivewireAlert::title("Ketidaksesuaian Data")
                    ->text("No. Rekam Medis Anda tidak sama dengan yang terdata di BPJS. Harap lakukan perubahan di Loket Pendaftaran.")
                    ->warning()
                    ->timer(5000)
                    ->show();
            } else {
                $this->isMRSame = true;
            }

            // Kirim flag canUseBpjs ke parent component
            $this->dispatch('setCanUseBpjs', $this->canUseBpjs);
        } catch (\Exception $e) {
            $this->isMRSame = false;
            $this->hasError = true;
            $this->errorCode = 500;
            $this->canUseBpjs = false;
            $this->errorMessage = $e->getMessage();
            $this->dispatch('setCanUseBpjs', $this->canUseBpjs);
            $this->dispatch('speak', text: "Terjadi kesalahan. Silakan hubungi petugas.");
        }
    }

    public function retryParticipantCheck()
    {
        $this->participantCheck();
    }

    // #[On('setParticipantInfo')]
    // public function setParticipantInfo(array $payload)
    // {
    //     $this->patient = $payload['patient'];
    //     $this->participantData = $payload['participantData'];
    // }

    public function next()
    {
        if (preg_match($this->phoneNumberExp, $this->patient?->no_tlp)) {
            $this->dispatch('setStep', step: 2);
        } else {
            Flux::modal('updatePhoneNumber')->show();
        }
    }

    public function skipPhoneValidation()
    {
        $this->dispatch('updatePhoneNumber', '000000000000');
        Flux::modal('updatePhoneNumber')->close();
        $this->dispatch('setStep', step: 2);
    }

    #[On('phone-updated')]
    public function checkNumberPhoneFormat($payload)
    {
        $this->newPhoneNumber = $payload['value'];
        $this->isInvalid = preg_match($this->phoneNumberExp, $this->newPhoneNumber) ? false : true;
    }

    #[On('phone-entered')]
    public function updatePhoneNumber($payload = null)
    {
        // Jika payload dikirim, gunakan nilai dari payload
        if ($payload && isset($payload['value'])) {
            $this->newPhoneNumber = $payload['value'];
            // Validasi ulang nomor HP dari payload
            $this->isInvalid = preg_match($this->phoneNumberExp, $this->newPhoneNumber) ? false : true;
        }

        if (!$this->isInvalid) {
            Flux::modal('updatePhoneNumber')
                ->close();
            $this->dispatch('updatePhoneNumber', $this->newPhoneNumber);
        }
    }
}
