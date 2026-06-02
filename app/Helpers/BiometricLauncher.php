<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class BiometricLauncher
{
    /**
     * Path ke aplikasi FRISTA
     */
    private string $fristaPath;

    /**
     * Path ke aplikasi Fingerprint
     */
    private string $fingerprintPath;

    /**
     * Username untuk login biometric
     */
    private string $username;

    /**
     * Password untuk login biometric
     */
    private string $password;

    public function __construct()
    {
        $this->fristaPath = config('biometric.frista_path');
        $this->fingerprintPath = config('biometric.fingerprint_path');
        $this->username = config('biometric.username');
        $this->password = config('biometric.password');
    }

    /**
     * Cek apakah aplikasi FRISTA tersedia di path yang ditentukan
     *
     * @return array{available: bool, path: string, message: string}
     */
    public function checkFristaAvailability(): array
    {
        if (empty($this->fristaPath)) {
            return [
                'available' => false,
                'path' => '',
                'message' => 'Path aplikasi FRISTA tidak dikonfigurasi di .env'
            ];
        }

        if (!file_exists($this->fristaPath)) {
            return [
                'available' => false,
                'path' => $this->fristaPath,
                'message' => "Aplikasi FRISTA tidak ditemukan di path: {$this->fristaPath}"
            ];
        }

        return [
            'available' => true,
            'path' => $this->fristaPath,
            'message' => 'Aplikasi FRISTA tersedia'
        ];
    }

    /**
     * Cek apakah aplikasi Fingerprint tersedia di path yang ditentukan
     *
     * @return array{available: bool, path: string, message: string}
     */
    public function checkFingerprintAvailability(): array
    {
        if (empty($this->fingerprintPath)) {
            return [
                'available' => false,
                'path' => '',
                'message' => 'Path aplikasi Fingerprint tidak dikonfigurasi di .env'
            ];
        }

        if (!file_exists($this->fingerprintPath)) {
            return [
                'available' => false,
                'path' => $this->fingerprintPath,
                'message' => "Aplikasi Fingerprint tidak ditemukan di path: {$this->fingerprintPath}"
            ];
        }

        return [
            'available' => true,
            'path' => $this->fingerprintPath,
            'message' => 'Aplikasi Fingerprint tersedia'
        ];
    }

    /**
     * Launch aplikasi FRISTA
     *
     * @param string|null $participantNumber Nomor peserta BPJS (opsional)
     * @return array{success: bool, message: string, credentials: array}
     */
    public function launchFrista(?string $participantNumber = null): array
    {
        try {
            $availability = $this->checkFristaAvailability();

            if (!$availability['available']) {
                return [
                    'success' => false,
                    'message' => $availability['message'],
                    'credentials' => []
                ];
            }

            // Coba launch aplikasi dengan berbagai metode
            $launched = $this->launchApplication($this->fristaPath, $participantNumber);

            if (!$launched) {
                return [
                    'success' => false,
                    'message' => 'Gagal membuka aplikasi FRISTA. Silakan buka manual.',
                    'credentials' => $this->getCredentials()
                ];
            }

            return [
                'success' => true,
                'message' => 'Aplikasi FRISTA berhasil dibuka. Silakan login menggunakan kredensial yang disediakan.',
                'credentials' => $this->getCredentials(),
                'participant_number' => $participantNumber
            ];
        } catch (\Exception $e) {
            Log::error('Error launching FRISTA: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'credentials' => $this->getCredentials()
            ];
        }
    }

    /**
     * Launch aplikasi Fingerprint
     *
     * @param string|null $participantNumber Nomor peserta BPJS (opsional)
     * @return array{success: bool, message: string, credentials: array}
     */
    public function launchFingerprint(?string $participantNumber = null): array
    {
        try {
            $availability = $this->checkFingerprintAvailability();

            if (!$availability['available']) {
                return [
                    'success' => false,
                    'message' => $availability['message'],
                    'credentials' => []
                ];
            }

            // Coba launch aplikasi dengan berbagai metode
            $launched = $this->launchApplication($this->fingerprintPath, $participantNumber);

            if (!$launched) {
                return [
                    'success' => false,
                    'message' => 'Gagal membuka aplikasi Fingerprint. Silakan buka manual.',
                    'credentials' => $this->getCredentials()
                ];
            }

            return [
                'success' => true,
                'message' => 'Aplikasi Fingerprint berhasil dibuka. Silakan login menggunakan kredensial yang disediakan.',
                'credentials' => $this->getCredentials(),
                'participant_number' => $participantNumber
            ];
        } catch (\Exception $e) {
            Log::error('Error launching Fingerprint: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'credentials' => $this->getCredentials()
            ];
        }
    }

    /**
     * Launch aplikasi menggunakan berbagai metode
     *
     * @param string $path Path ke aplikasi
     * @param string|null $participantNumber Nomor peserta (jika supported)
     * @return bool
     */
    private function launchApplication(string $path, ?string $participantNumber = null): bool
    {
        // Escape path dengan quotes untuk handle spasi
        $escapedPath = '"' . $path . '"';

        // Metode 1: Gunakan popen untuk non-blocking execution (Best for Windows)
        try {
            // Windows: gunakan start untuk non-blocking
            if (PHP_OS_FAMILY === 'Windows') {
                pclose(popen("start /B {$escapedPath}", 'r'));
                Log::info("Launched application via popen (Windows): {$path}");
                return true;
            } else {
                // Linux/Mac: gunakan nohup untuk non-blocking
                pclose(popen("nohup {$escapedPath} > /dev/null 2>&1 &", 'r'));
                Log::info("Launched application via popen (Unix): {$path}");
                return true;
            }
        } catch (\Exception $e) {
            Log::warning("popen failed: " . $e->getMessage());
        }

        // Metode 2: Fallback ke shell_exec
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                shell_exec("start {$escapedPath}");
                Log::info("Launched application via shell_exec (Windows): {$path}");
                return true;
            } else {
                shell_exec("nohup {$escapedPath} > /dev/null 2>&1 &");
                Log::info("Launched application via shell_exec (Unix): {$path}");
                return true;
            }
        } catch (\Exception $e) {
            Log::warning("shell_exec failed: " . $e->getMessage());
        }

        // Metode 3: Fallback ke exec
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                exec("start {$escapedPath}");
                Log::info("Launched application via exec (Windows): {$path}");
                return true;
            } else {
                exec("nohup {$escapedPath} > /dev/null 2>&1 &");
                Log::info("Launched application via exec (Unix): {$path}");
                return true;
            }
        } catch (\Exception $e) {
            Log::warning("exec failed: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Dapatkan kredensial untuk login
     *
     * @return array{username: string, password: string}
     */
    public function getCredentials(): array
    {
        return [
            'username' => $this->username,
            'password' => $this->password
        ];
    }

    /**
     * Format kredensial untuk clipboard
     *
     * @return string
     */
    public function getCredentialsForClipboard(): string
    {
        return "Username: {$this->username}\nPassword: {$this->password}";
    }

    /**
     * Dapatkan instruksi delay yang direkomendasikan (dalam milidetik)
     *
     * @return array{
     *     app_launch: int,
     *     app_load: int,
     *     after_login: int,
     *     after_participant_number: int
     * }
     */
    public function getRecommendedDelays(): array
    {
        return [
            'app_launch' => 1000,           // Delay setelah launch aplikasi
            'app_load' => 3000,             // Tunggu aplikasi load sepenuhnya
            'after_login' => 2000,          // Delay setelah submit login
            'after_participant_number' => 1500, // Delay setelah isi nomor peserta
        ];
    }
}
