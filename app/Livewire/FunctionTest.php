<?php

namespace App\Livewire;

use App\Helpers\BiometricLauncher;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class FunctionTest extends Component
{
    public array $connectionResults = [];
    public array $functionResults   = [];

    /** Jalankan semua uji koneksi */
    public function runAllConnections(): void
    {
        $this->connectionResults = [];
        $this->testDbMain();
        $this->testDbSimrs();
        $this->testBpjsVclaim();
        $this->testBpjsAntrol();
    }

    /** Uji koneksi database utama (APM) */
    public function testDbMain(): void
    {
        try {
            DB::connection('mariadb')->getPdo();
            $db = config('database.connections.mariadb.database');
            $this->addConnection('db_main', 'Database APM', 'success', "Terhubung ke database [{$db}]");
            ActivityLogService::success('database', 'uji_koneksi_db_apm', "Koneksi ke database APM [{$db}] berhasil");
        } catch (\Throwable $e) {
            $this->addConnection('db_main', 'Database APM', 'error', $e->getMessage());
            ActivityLogService::error('database', 'uji_koneksi_db_apm', $e->getMessage());
        }
    }

    /** Uji koneksi database SIMRS */
    public function testDbSimrs(): void
    {
        try {
            DB::connection('simrs')->getPdo();
            $db = config('database.connections.simrs.database');
            $this->addConnection('db_simrs', 'Database SIMRS', 'success', "Terhubung ke database [{$db}]");
            ActivityLogService::success('database', 'uji_koneksi_db_simrs', "Koneksi ke database SIMRS [{$db}] berhasil");
        } catch (\Throwable $e) {
            $this->addConnection('db_simrs', 'Database SIMRS', 'error', $e->getMessage());
            ActivityLogService::error('database', 'uji_koneksi_db_simrs', $e->getMessage());
        }
    }

    /** Uji koneksi BPJS Vclaim */
    public function testBpjsVclaim(): void
    {
        $url = env('VCLAIM_BPJS_URL', 'https://apijkn.bpjs-kesehatan.go.id/vclaim-rest');
        try {
            $response = Http::timeout(10)->get($url . '/SEP/{parameter}');
            $status = $response->status();
            if ($response->serverError()) {
                $this->addConnection('bpjs_vclaim', 'BPJS Vclaim', 'error', "Server error HTTP {$status}");
                ActivityLogService::error('bpjs', 'uji_koneksi_vclaim', "Server error HTTP {$status}", ['url' => $url]);
            } else {
                $this->addConnection('bpjs_vclaim', 'BPJS Vclaim', 'success', "Server dapat dijangkau (HTTP {$status})");
                ActivityLogService::success('bpjs', 'uji_koneksi_vclaim', "Server Vclaim dapat dijangkau (HTTP {$status})", ['url' => $url]);
            }
        } catch (\Throwable $e) {
            $this->addConnection('bpjs_vclaim', 'BPJS Vclaim', 'error', $e->getMessage());
            ActivityLogService::error('bpjs', 'uji_koneksi_vclaim', $e->getMessage(), ['url' => $url]);
        }
    }

    /** Uji koneksi BPJS Antrol */
    public function testBpjsAntrol(): void
    {
        $url = env('ANTROL_BPJS_URL', 'https://apijkn.bpjs-kesehatan.go.id/antreanrs');
        try {
            $response = Http::timeout(10)->get($url);
            $status = $response->status();
            if ($response->serverError()) {
                $this->addConnection('bpjs_antrol', 'BPJS Antrol', 'error', "Server error HTTP {$status}");
                ActivityLogService::error('bpjs', 'uji_koneksi_antrol', "Server error HTTP {$status}", ['url' => $url]);
            } else {
                $this->addConnection('bpjs_antrol', 'BPJS Antrol', 'success', "Server dapat dijangkau (HTTP {$status})");
                ActivityLogService::success('bpjs', 'uji_koneksi_antrol', "Server Antrol dapat dijangkau (HTTP {$status})", ['url' => $url]);
            }
        } catch (\Throwable $e) {
            $this->addConnection('bpjs_antrol', 'BPJS Antrol', 'error', $e->getMessage());
            ActivityLogService::error('bpjs', 'uji_koneksi_antrol', $e->getMessage(), ['url' => $url]);
        }
    }

    /** Dipanggil dari JS setelah biometricBpjsTrigger dikirim */
    public function markBiometricTriggered(string $mode): void
    {
        $label = $mode === 'fingerprint' ? 'Fingerprint' : 'FRISTA';
        $this->addFunction($mode, $label, 'success', "Trigger {$label} berhasil dikirim ke BPJS Native Host.");
        ActivityLogService::success($mode, "uji_trigger_{$mode}", "Trigger {$label} dikirim ke BPJS Native Host");
    }

    /** Dipanggil dari JS setelah TTS di-trigger */
    public function markTtsTriggered(): void
    {
        $this->addFunction('tts', 'TTS (Text to Speech)', 'success', 'Suara TTS berhasil diputar via Web Speech API.');
        ActivityLogService::success('system', 'uji_trigger_tts', 'Trigger TTS berhasil diputar');
    }

    private function addConnection(string $key, string $label, string $status, string $message): void
    {
        $this->connectionResults[$key] = [
            'label'   => $label,
            'status'  => $status,
            'message' => $message,
            'time'    => now()->format('H:i:s'),
        ];
    }

    private function addFunction(string $key, string $label, string $status, string $message): void
    {
        $this->functionResults[$key] = [
            'label'   => $label,
            'status'  => $status,
            'message' => $message,
            'time'    => now()->format('H:i:s'),
        ];
    }

    public function render()
    {
        return view('livewire.function-test')
            ->layout('components.layouts.console-box', ['title' => 'Function Test']);
    }
}
