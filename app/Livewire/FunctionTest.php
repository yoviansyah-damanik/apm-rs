<?php

namespace App\Livewire;

use App\Services\ActivityLogService;
use App\Services\BpjsService;
use App\Services\QueueService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class FunctionTest extends Component
{
    public array  $connectionResults     = [];
    public array  $functionResults       = [];
    public array  $bpjsResults           = [];
    public string $bpjsParticipantNumber = '';
    public string $dbApmVersion          = '-';
    public string $dbSimrsVersion        = '-';

    public function mount(): void
    {
        try {
            $this->dbApmVersion = DB::connection('mariadb')->selectOne('SELECT VERSION() as v')->v;
        } catch (\Throwable) {}

        try {
            $this->dbSimrsVersion = DB::connection('simrs')->selectOne('SELECT VERSION() as v')->v;
        } catch (\Throwable) {}
    }

    // -------------------------------------------------------------------------
    // Uji Koneksi
    // -------------------------------------------------------------------------

    public function runAllConnections(): void
    {
        $this->connectionResults = [];
        $this->testDbMain();
        $this->testDbSimrs();
        $this->testBpjsVclaim();
        $this->testBpjsAntrol();
    }

    public function testDbMain(): void
    {
        try {
            DB::connection('mariadb')->getPdo();
            $db      = config('database.connections.mariadb.database');
            $version = DB::connection('mariadb')->selectOne('SELECT VERSION() as version')->version;
            $this->addConnection('db_main', 'Database APM', 'success', "Terhubung ke [{$db}] · {$version}");
            ActivityLogService::success('database', 'uji_koneksi_db_apm', "Koneksi ke database APM [{$db}] berhasil", ['version' => $version]);
        } catch (\Throwable $e) {
            $this->addConnection('db_main', 'Database APM', 'error', $e->getMessage());
            ActivityLogService::error('database', 'uji_koneksi_db_apm', $e->getMessage());
        }
    }

    public function testDbSimrs(): void
    {
        try {
            DB::connection('simrs')->getPdo();
            $db      = config('database.connections.simrs.database');
            $version = DB::connection('simrs')->selectOne('SELECT VERSION() as version')->version;
            $this->addConnection('db_simrs', 'Database SIMRS', 'success', "Terhubung ke [{$db}] · {$version}");
            ActivityLogService::success('database', 'uji_koneksi_db_simrs', "Koneksi ke database SIMRS [{$db}] berhasil", ['version' => $version]);
        } catch (\Throwable $e) {
            $this->addConnection('db_simrs', 'Database SIMRS', 'error', $e->getMessage());
            ActivityLogService::error('database', 'uji_koneksi_db_simrs', $e->getMessage());
        }
    }

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

    // -------------------------------------------------------------------------
    // Uji API BPJS
    // -------------------------------------------------------------------------

    public function runAllBpjsTests(): void
    {
        $this->bpjsResults = [];
        $this->testBpjsParticipant();
        $this->testBpjsReferences();
        $this->testBpjsControlLetters();
    }

    public function testBpjsParticipant(): void
    {
        if (empty(trim($this->bpjsParticipantNumber))) {
            $this->addBpjs('participant', 'Cek Peserta', 'error', 'Nomor kartu / NIK belum diisi.');
            return;
        }
        try {
            $payload = (new BpjsService(trim($this->bpjsParticipantNumber)))->getParticipant();

            if (!isset($payload['success']) || $payload['success'] === false) {
                $code = $payload['data']['metaData']['code'] ?? '?';
                $msg  = $payload['data']['metaData']['message'] ?? 'Gagal';
                $this->addBpjs('participant', 'Cek Peserta', 'error', "[{$code}] {$msg}");
                ActivityLogService::error('bpjs', 'uji_cek_peserta', "[{$code}] {$msg}", ['no_kartu' => $this->bpjsParticipantNumber]);
            } else {
                $nama    = $payload['data']['peserta']['nama'] ?? '-';
                $noKartu = $payload['data']['peserta']['noKartu'] ?? $this->bpjsParticipantNumber;
                $this->addBpjs('participant', 'Cek Peserta', 'success', "Peserta ditemukan: {$nama} ({$noKartu})");
                ActivityLogService::success('bpjs', 'uji_cek_peserta', "Peserta ditemukan: {$nama}", ['no_kartu' => $noKartu]);
            }
        } catch (\Throwable $e) {
            $this->addBpjs('participant', 'Cek Peserta', 'error', $e->getMessage());
            ActivityLogService::error('bpjs', 'uji_cek_peserta', $e->getMessage(), ['no_kartu' => $this->bpjsParticipantNumber]);
        }
    }

    public function testBpjsReferences(): void
    {
        if (empty(trim($this->bpjsParticipantNumber))) {
            $this->addBpjs('references', 'Cek Rujukan', 'error', 'Nomor kartu / NIK belum diisi.');
            return;
        }
        try {
            $payload   = (new BpjsService(trim($this->bpjsParticipantNumber)))->getListOfReferences();
            $fktpCount = is_array($payload['data']['fktp'] ?? null) ? count($payload['data']['fktp']) : 0;
            $rsCount   = is_array($payload['data']['rs'] ?? null)   ? count($payload['data']['rs'])   : 0;

            if ($fktpCount === 0 && $rsCount === 0) {
                $this->addBpjs('references', 'Cek Rujukan', 'error', 'Tidak ada rujukan aktif ditemukan.');
                ActivityLogService::error('bpjs', 'uji_cek_rujukan', 'Tidak ada rujukan aktif', ['no_kartu' => $this->bpjsParticipantNumber]);
            } else {
                $this->addBpjs('references', 'Cek Rujukan', 'success', "FKTP: {$fktpCount} rujukan · RS: {$rsCount} rujukan");
                ActivityLogService::success('bpjs', 'uji_cek_rujukan', "FKTP: {$fktpCount}, RS: {$rsCount}", ['no_kartu' => $this->bpjsParticipantNumber]);
            }
        } catch (\Throwable $e) {
            $this->addBpjs('references', 'Cek Rujukan', 'error', $e->getMessage());
            ActivityLogService::error('bpjs', 'uji_cek_rujukan', $e->getMessage(), ['no_kartu' => $this->bpjsParticipantNumber]);
        }
    }

    public function testBpjsControlLetters(): void
    {
        if (empty(trim($this->bpjsParticipantNumber))) {
            $this->addBpjs('control_letters', 'Cek Surat Kontrol', 'error', 'Nomor kartu / NIK belum diisi.');
            return;
        }
        try {
            $payload = (new BpjsService(trim($this->bpjsParticipantNumber)))->getListOfControlLetters();

            if (isset($payload['success']) && $payload['success'] === false) {
                $msg = $payload['data']['metaData']['message'] ?? 'Gagal';
                $this->addBpjs('control_letters', 'Cek Surat Kontrol', 'error', $msg);
                ActivityLogService::error('bpjs', 'uji_cek_surat_kontrol', $msg, ['no_kartu' => $this->bpjsParticipantNumber]);
            } else {
                $count = is_array($payload['data'] ?? null) ? count($payload['data']) : 0;
                $this->addBpjs('control_letters', 'Cek Surat Kontrol', 'success', "{$count} surat kontrol ditemukan.");
                ActivityLogService::success('bpjs', 'uji_cek_surat_kontrol', "{$count} surat kontrol ditemukan", ['no_kartu' => $this->bpjsParticipantNumber]);
            }
        } catch (\Throwable $e) {
            $this->addBpjs('control_letters', 'Cek Surat Kontrol', 'error', $e->getMessage());
            ActivityLogService::error('bpjs', 'uji_cek_surat_kontrol', $e->getMessage(), ['no_kartu' => $this->bpjsParticipantNumber]);
        }
    }

    // -------------------------------------------------------------------------
    // Uji Fungsi
    // -------------------------------------------------------------------------

    public function runAllFunctions(): void
    {
        $this->functionResults = [];
        $this->testQueueStatus();
    }

    public function testQueueStatus(): void
    {
        try {
            $queueService = new QueueService();
            $next      = $queueService->getNextQueueNumber();
            $nextPrio  = $queueService->getNextPriorityQueueNumber();
            $remaining = $queueService->getRemainingQueue();

            $this->addFunction('queue_status', 'Status Antrean', 'success',
                "Berikutnya: {$next} · Prioritas: {$nextPrio} · Sisa: {$remaining}");
            ActivityLogService::success('system', 'uji_status_antrean', "Antrean berikutnya: {$next}, sisa: {$remaining}");
        } catch (\Throwable $e) {
            $this->addFunction('queue_status', 'Status Antrean', 'error', $e->getMessage());
            ActivityLogService::error('system', 'uji_status_antrean', $e->getMessage());
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

    /** Dipanggil dari JS setelah perintah cetak dikirim */
    public function markPrinterTriggered(bool $success, string $message = ''): void
    {
        if ($success) {
            $this->addFunction('printer', 'Uji Cetak', 'success', 'Perintah cetak berhasil dikirim ke printer.');
            ActivityLogService::success('system', 'uji_printer', 'Perintah cetak berhasil dikirim');
        } else {
            $this->addFunction('printer', 'Uji Cetak', 'error', $message ?: 'Printer tidak merespons.');
            ActivityLogService::error('system', 'uji_printer', $message ?: 'Printer tidak merespons');
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function addConnection(string $key, string $label, string $status, string $message): void
    {
        $this->connectionResults[$key] = compact('label', 'status', 'message') + ['time' => now()->format('H:i:s')];
    }

    private function addFunction(string $key, string $label, string $status, string $message): void
    {
        $this->functionResults[$key] = compact('label', 'status', 'message') + ['time' => now()->format('H:i:s')];
    }

    private function addBpjs(string $key, string $label, string $status, string $message): void
    {
        $this->bpjsResults[$key] = compact('label', 'status', 'message') + ['time' => now()->format('H:i:s')];
    }

    public function render()
    {
        return view('livewire.function-test')
            ->layout('components.layouts.console-box', ['title' => 'Function Test']);
    }
}
