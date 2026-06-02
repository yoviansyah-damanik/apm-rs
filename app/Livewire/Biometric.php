<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\BpjsService;
use App\Helpers\BiometricLauncher;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Biometric extends Component
{
    public $participantData;

    public $biometricStatus;
    public $username;
    public $password;

    public function mount()
    {
        $this->username = env('BIOMETRIC_USERNAME', 'SET DULU DI ENV');
        $this->password = env('BIOMETRIC_PASSWORD', 'PASSWORD BELUM ADA');
        $this->checkBiometricStatus();
    }

    public function render()
    {
        return view('livewire.biometric');
    }

    public function placeholder()
    {
        return view('placeholders.biometric');
    }

    public function setBiometricStatus()
    {
        $this->dispatch('biometricStatus', true);
    }

    public function checkBiometricStatus()
    {
        $bpjsService = new BpjsService($this->participantData['peserta']['noKartu']);
        $response = $bpjsService->getBiometricStatus();

        // Jika response tidak memiliki data, set status error
        if (!isset($response['data'])) {
            $this->biometricStatus = [
                'kode' => 0,
                'status' => 'Gagal mendapatkan status biometrik',
                'message' => $response['message'] ?? 'Tidak dapat terhubung ke server BPJS',
                'biometric_required' => true
            ];

            LivewireAlert::title("Gagal mendapatkan status biometrik BPJS")
                ->text("Terjadi kesalahan saat mendapatkan status biometrik dari BPJS. Silakan coba lagi nanti. " . ($response['message'] ?? ''))
                ->withConfirmButton()
                ->confirmButtonText('OK')
                ->timer(0)
                ->error()
                ->show();
            return;
        }

        $this->biometricStatus = $response['data'];

        // Cek status validasi biometrik
        $kode = $this->biometricStatus['kode'] ?? null;
        $biometricRequired = $this->biometricStatus['biometric_required'] ?? null;

        // Jika kode = 1 atau biometric_required = false, validasi berhasil
        if ($kode == 1 || $biometricRequired === false) {
            // LivewireAlert::title("Validasi Biometrik Berhasil")
            //     ->text($this->biometricStatus['status'] ?? $this->biometricStatus['message'] ?? 'Silakan klik tombol Lanjutkan untuk melanjutkan proses')
            //     ->success()
            //     ->timer(5000)
            //     ->show();
        } else {
            // Validasi belum berhasil, tampilkan notifikasi peringatan
            // LivewireAlert::title("Validasi Biometrik Diperlukan")
            //     ->text("Kode: " . ($kode ?? 'N/A') . " - " . ($this->biometricStatus['message'] ?? $this->biometricStatus['status'] ?? 'Silakan lakukan validasi biometrik'))
            //     ->warning()
            //     ->timer(5000)
            //     ->show();
        }
    }

    /**
     * Dapatkan kredensial untuk ditampilkan di UI
     */
    public function getCredentials()
    {
        $launcher = new BiometricLauncher();
        return $launcher->getCredentials();
    }

    /**
     * Launch aplikasi FRISTA dan copy credentials ke clipboard
     */
    // public function launchFrista()
    // {
    //     $launcher = new BiometricLauncher();
    //     $participantNumber = $this->participantData['peserta']['noKartu'] ?? null;

    //     // Cek availability dulu
    //     $availability = $launcher->checkFristaAvailability();

    //     if (!$availability['available']) {
    //         LivewireAlert::title("Aplikasi Tidak Tersedia")
    //             ->text($availability['message'])
    //             ->warning()
    //             ->timer(0)
    //             ->show();
    //         return;
    //     }

    //     // Launch aplikasi
    //     $result = $launcher->launchFrista($participantNumber);

    //     if ($result['success']) {
    //         // Dispatch event ke JavaScript untuk handle clipboard dan delays
    //         $this->dispatch('biometric-app-launched', [
    //             'app' => 'frista',
    //             'credentials' => $result['credentials'],
    //             'participant_number' => $result['participant_number'] ?? null,
    //             'delays' => $launcher->getRecommendedDelays()
    //         ]);

    //         LivewireAlert::title("Aplikasi FRISTA Dibuka")
    //             ->text($result['message'] . "\n\nKredensial telah disalin ke clipboard.")
    //             ->success()
    //             ->timer(5000)
    //             ->show();
    //     } else {
    //         LivewireAlert::title("Gagal Membuka Aplikasi")
    //             ->text($result['message'])
    //             ->error()
    //             ->timer(0)
    //             ->show();
    //     }
    // }

    /**
     * Launch aplikasi Fingerprint secara async tanpa menunggu proses selesai
     */
    // public function launchFingerprint()
    // {
    //     $pathJarFinger = env('PATH_JAR_FINGER');

    //     if (empty($pathJarFinger)) {
    //         LivewireAlert::title("Konfigurasi Tidak Ditemukan")
    //             ->text("PATH_JAR_FINGER belum dikonfigurasi di file .env")
    //             ->error()
    //             ->timer(0)
    //             ->show();
    //         return;
    //     }

    //     if (!file_exists($pathJarFinger)) {
    //         LivewireAlert::title("File Tidak Ditemukan")
    //             ->text("File aplikasi fingerprint tidak ditemukan di: " . $pathJarFinger)
    //             ->error()
    //             ->timer(0)
    //             ->show();
    //         return;
    //     }

    //     $cmd = "java -jar \"" . $pathJarFinger . "\" " . $this->participantData['peserta']['noKartu'];

    //     exec($cmd);

    //     LivewireAlert::title("Membuka Aplikasi Fingerprint")
    //         ->text("Aplikasi Fingerprint BPJS sedang dibuka di background.")
    //         ->success()
    //         ->timer(3000)
    //         ->show();
    // }
}
