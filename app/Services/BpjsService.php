<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Register;
use App\Enums\PurposeOfVisit;
use Illuminate\Support\Facades\Date;

class BpjsService
{
    use BpjsServices\QueueService;
    use BpjsServices\VclaimService;

    private string $participantNumber;
    private string $consId;
    private string $secretKey;
    private string $userKey;
    private string $fpBpjsProcessName = 'After.exe'; // Sesuaikan dengan nama process aplikasi
    private string $fpBpjsWindowTitle = 'Aplikasi Registrasi Sidik Jari'; // Sesuaikan dengan title window
    private array $signature;
    private string $service;
    private string $baseUrl;
    private string $participantNumberType = 'noka';
    private array $participantData = [];

    /**
     * Inisiasi noka dan service terlebih dahulu
     * @param string $participantNumber     NIK | No Kartu BPJS
     * @param string $service               vclaim (default) | antrol
     */
    public function __construct(string $participantNumber, string $service = 'vclaim')
    {
        $this->setService($service);

        // Set participant number dan ambil data jika input adalah NIK
        $this->participantNumber = $participantNumber;
        if (strlen($this->participantNumber) == 16) {
            $this->participantNumberType = 'nik';
        }
    }


    public function setService(string $service): static
    {
        $availableServices = ['vclaim', 'antrol'];

        if (!in_array($service, $availableServices)) {
            throw new \InvalidArgumentException("{$service} tidak tersedia. Service yang tersedia: " . implode(', ', $availableServices));
        }

        $this->service = $service;
        $prefix = strtoupper($service);

        $this->baseUrl = env("{$prefix}_BPJS_URL", '');
        $this->consId = env("{$prefix}_CONS_ID_BPJS", '');
        $this->secretKey = env("{$prefix}_SECRET_KEY_BPJS", '');
        $this->userKey = env("{$prefix}_USER_KEY_BPJS", '');

        $this->generateSignature();

        return $this;
    }

    private function generateSignature(): void
    {
        try {
            // Simpan timezone saat ini
            $originalTimezone = date_default_timezone_get();

            // Set timezone ke UTC sementara untuk signature
            date_default_timezone_set('UTC');

            // Compute the timestamp
            $timestamp = strval(time() - strtotime('1970-01-01 00:00:00'));

            // Compute the signature by hashing the salt with the secret key
            $signatureHash = hash_hmac('sha256', $this->consId . "&" . $timestamp, $this->secretKey, true);

            // Base64 encode the signature
            $this->signature = [
                'timestamp' => $timestamp,
                'value' => base64_encode($signatureHash)
            ];

            // Kembalikan timezone ke semula
            date_default_timezone_set($originalTimezone);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    private function getHeaders(
        string $contentType = "application/x-www-form-urlencoded"
    ): array {
        $headers = [
            'X-cons-id' => $this->consId,
            'X-timestamp' => $this->signature['timestamp'],
            'X-signature' => $this->signature['value'],
            'Content-Type' => $contentType
        ];

        return [...$headers, 'user_key' => $this->userKey];
    }

    /**
     * Cek apakah nomor peserta termasuk No Kartu BPJS atau NIK
     * @param   string      $participantNumber      Nomor Peserta
     * @param   string      $service                'vclaim' (default) | 'antrol'
     * @return  array
     */
    private function validateParticipantNumber(): array
    {
        if (!in_array(strlen($this->participantNumber), [13, 16])) {
            return [
                'success' => false,
                'message' => 'Format nomor peserta tidak sesuai. Silahkan hubungi Loket Pendaftaran untuk memperbarui kepesertaan anda.'
            ];
        } else {
            return [
                'success' => true,
                'message' => 'Format nomor peserta sesuai.',
                'data' => [
                    'participantNumber' => $this->participantNumber,
                    'participantNumberFormat' => strlen($this->participantNumber) == 16 ? 'nik' : ($this->service == 'antrol' ? 'noka' : 'nokartu'),
                ]
            ];
        }
    }
    private function fpBpjsProcessName()
    {
    }
    private function fristaBpjsProcessName()
    {
    }

    /**
     * Cek apakah aplikasi FP BPJS sedang berjalan
     */
    public function isApplicationRunning(): bool
    {
        try {
            $output = shell_exec("tasklist /FI \"IMAGENAME eq " . $this->fpBpjsProcessName . "\"");
            return $output !== null && strpos($output, $this->fpBpjsProcessName) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Buka aplikasi FP BPJS jika belum berjalan
     */
    public function openApplication(string $applicationPath): bool
    {
        try {
            if (!$this->isApplicationRunning()) {
                if (PHP_OS_FAMILY === 'Windows') {
                    pclose(popen("start \"\" \"{$applicationPath}\"", 'r'));
                } else {
                    shell_exec("nohup \"{$applicationPath}\" > /dev/null 2>&1 &");
                }
                // Tunggu aplikasi terbuka
                sleep(3);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Fokuskan window aplikasi FP BPJS
     */
    public function focusApplication(): bool
    {
        try {
            // Menggunakan PowerShell untuk fokus window
            $script = "
                Add-Type -AssemblyName Microsoft.VisualBasic
                [Microsoft.VisualBasic.Interaction]::AppActivate('" . $this->fpBpjsWindowTitle . "')
            ";

            shell_exec("powershell -Command \"{$script}\"");
            sleep(1);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Cek apakah user sudah login (deteksi field No BPJS visible)
     */
    public function isUserLoggedIn(): bool
    {
        // Implementasi ini tergantung pada struktur UI aplikasi
        // Bisa menggunakan screenshot analysis atau window element detection

        // Contoh sederhana: cek apakah window title berubah setelah login
        try {
            $output = shell_exec("tasklist /v /FI \"IMAGENAME eq " . $this->fpBpjsProcessName . "\"");
            // Analisis output untuk menentukan status login
            return $output !== null && strpos($output, 'Dashboard') !== false; // Contoh
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Kirim keystroke ke aplikasi yang sedang aktif
     */
    private function sendKeys(string $keys): void
    {
        // Menggunakan PowerShell SendKeys
        $script = "
            Add-Type -AssemblyName System.Windows.Forms
            [System.Windows.Forms.SendKeys]::SendWait('{$keys}')
        ";

        shell_exec("powershell -Command \"{$script}\"");
        usleep(500000); // 0.5 detik delay
    }

    /**
     * Login ke aplikasi FP BPJS
     */
    public function login(string $username, string $password): bool
    {
        try {
            if (!$this->focusApplication()) {
                return false;
            }

            // Pastikan cursor di field username (Tab untuk navigasi jika perlu)
            $this->sendKeys('{TAB}');

            // Clear field dan isi username
            $this->sendKeys('^a'); // Ctrl+A
            $this->sendKeys($username);

            // Pindah ke field password
            $this->sendKeys('{TAB}');

            // Clear field dan isi password
            $this->sendKeys('^a'); // Ctrl+A
            $this->sendKeys($password);

            // Submit form (Enter atau click login button)
            $this->sendKeys('{ENTER}');

            // Tunggu proses login
            sleep(2);

            return $this->isUserLoggedIn();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Isi nomor BPJS
     */
    public function fillBpjsNumber(string $bpjsNumber): bool
    {
        try {
            if (!$this->focusApplication()) {
                return false;
            }

            // Navigate to BPJS number field
            // Ini tergantung pada layout aplikasi, mungkin perlu beberapa TAB
            $this->sendKeys('{TAB}{TAB}'); // Contoh navigasi

            // Clear field dan isi nomor BPJS
            $this->sendKeys('^a'); // Ctrl+A untuk select all
            $this->sendKeys($bpjsNumber);

            // Optional: Submit atau pindah ke field berikutnya
            $this->sendKeys('{ENTER}');

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Proses automasi lengkap
     */
    public function automateProcess(string $applicationPath, string $username, string $password, string $bpjsNumber): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'steps' => []
        ];

        try {
            // Step 1: Buka aplikasi jika belum berjalan
            if (!$this->isApplicationRunning()) {
                $result['steps'][] = 'Membuka aplikasi FP BPJS...';
                if (!$this->openApplication($applicationPath)) {
                    $result['message'] = 'Gagal membuka aplikasi FP BPJS';
                    return $result;
                }
            }

            // Step 2: Fokus ke aplikasi
            $result['steps'][] = 'Mengaktifkan window aplikasi...';
            if (!$this->focusApplication()) {
                $result['message'] = 'Gagal mengaktifkan window aplikasi';
                return $result;
            }

            // Step 3: Cek status login
            if (!$this->isUserLoggedIn()) {
                $result['steps'][] = 'Melakukan login...';
                if (!$this->login($username, $password)) {
                    $result['message'] = 'Gagal melakukan login';
                    return $result;
                }
            } else {
                $result['steps'][] = 'User sudah login';
            }

            // Step 4: Isi nomor BPJS
            $result['steps'][] = 'Mengisi nomor BPJS...';
            if (!$this->fillBpjsNumber($bpjsNumber)) {
                $result['message'] = 'Gagal mengisi nomor BPJS';
                return $result;
            }

            $result['success'] = true;
            $result['message'] = 'Automasi berhasil completed';
            $result['steps'][] = 'Proses automasi selesai';
        } catch (\Exception $e) {
            $result['message'] = 'Error: ' . $e->getMessage();
        }

        return $result;
    }
}
