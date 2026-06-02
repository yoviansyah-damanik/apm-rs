<?php

namespace App\Livewire\ParticipantChecker;

use App\Models\Patient;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\BpjsService;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Search extends Component
{
    public $participantNumber;
    public $participantData;
    public bool $isFound = false;
    public $idNumber = '';
    public function render()
    {
        return view('livewire.participant-checker.search');
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

        if ($this->idNumber == '' || !in_array(strlen($this->idNumber), [6, 13, 16])) {
            LivewireAlert::title("Format nomor identitas salah!")
                ->text("Format nomor identitas yang anda masukkan salah. 6 digit untuk Nomor Rekam Medis, 13 digit untuk nomor kartu BPJS, atau 16 digit untuk NIK.")
                ->withConfirmButton() // Enables button with default text
                ->confirmButtonText('OK')
                ->timer(0)
                ->warning()
                ->show();
        } else {
            $participantNumber = null;
            if (!in_array(strlen($this->idNumber), [13, 16])) {
                $patient = Patient::where('no_rkm_medis', $this->idNumber)
                    ->first();

                if (!$patient) {
                    LivewireAlert::title("Pasien tidak ditemukan")
                        ->text("Silahkan periksa kembali nomor RM anda. Jika anda pasien baru, silahkan pilih menu Antrean Loket untuk pendaftaran pasien baru.")
                        ->withConfirmButton()
                        ->withCancelButton()
                        ->confirmButtonText('Ambil Antrean')
                        ->onConfirm('redirectToAmbilAntrean')
                        ->timer(0)
                        ->warning()
                        ->show();
                }

                $participantNumber = $patient?->no_peserta ?? $patient?->no_ktp;
            } else {
                $participantNumber = $this->idNumber;
            }

            if (in_array(strlen($participantNumber), [13, 16])) {
                $bpjsService = new BpjsService($participantNumber);
                $payload = $bpjsService->getParticipant();

                $errorCode = 200;
                $errorMessage = '';
                $hasError = false;

                // Handle error response from BPJS
                $errorCode = $payload['data']['metaData']['code'] ?? 500;
                $errorMessage = $payload['data']['metaData']['message'] ?? 'Koneksi ke server BPJS gagal';

                // Check if request was successful
                if (isset($payload['success']) && $payload['success'] === false && isset($payload['data']['metaData']['code']) && !in_array($payload['data']['metaData']['code'], [200, 201, 202])) {
                    // Code selain 20X: Error sebenarnya
                    $hasError = true;

                    LivewireAlert::title("Koneksi Gagal")
                        ->text("Kode Error: {$errorCode} - {$errorMessage}")
                        ->error()
                        ->timer(5000)
                        ->show();
                    return;
                }

                if ($errorCode >= 200 && $errorCode < 300) {
                    // Code 20X (bukan 200): Data ada tapi ada kondisi khusus
                    $hasError = false;
                    LivewireAlert::title("Nomor Peserta: {$participantNumber}")
                        ->text("{$errorCode} - {$errorMessage}")
                        ->timer(0)
                        ->warning()
                        ->show();
                    return;
                }

                // Check if response has valid data structure
                if (!isset($payload['data']['peserta'])) {
                    $hasError = true;
                    $errorCode = 500;
                    $errorMessage = 'Response dari BPJS tidak memiliki struktur data yang valid';

                    LivewireAlert::title("Data Tidak Valid")
                        ->text($errorMessage)
                        ->warning()
                        ->timer(3000)
                        ->show();
                    return;
                }

                if ($hasError) {
                    LivewireAlert::title("Kesalahan pada saat menarik Data Peserta")
                        ->text($errorCode . " - " . $errorMessage)
                        ->error()
                        ->show();
                    return;
                }
                // SUCCESS - Process participant data
                $this->participantData = $payload['data'];

                $this->dispatch('setParticipantData', $this->participantData);
                $this->dispatch('setStep', 2);
            }
        }
    }

    public function redirectToAmbilAntrean()
    {
        return $this->redirectroute('new-patient', navigate: true);
    }
}
