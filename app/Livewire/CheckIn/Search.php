<?php

namespace App\Livewire\CheckIn;

use Flux\Flux;
use App\Models\JknRef;
use App\Models\Patient;
use App\Models\Register;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Services\BpjsService;
use Livewire\Attributes\Reactive;
use Illuminate\Support\Facades\DB;
use App\Exceptions\BpjsTimeoutException;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Search extends Component
{
    public $idNumber;
    public $participantData;
    public bool $isLoading = false;
    public Patient|null $patient;
    public $isInvalid = false;
    public bool $showProcessModal = false;
    public $jknBooking = null;

    public function mount()
    {
        $this->idNumber = 'B' . now()->format('Ymd');
    }

    public function render()
    {
        return view('livewire.check-in.search');
    }

    #[On('numpad-entered')]
    public function checkPatient($payload = null): void
    {
        // Jika payload dikirim, gunakan nilai dari payload
        if ($payload && isset($payload['value'])) {
            $this->idNumber = $payload['value'];
        }

        // Validasi format kode booking
        if (Str::length($this->idNumber) != 15) {
            LivewireAlert::warning()
                ->title('Perhatian!')
                ->text('Format kode booking tidak sesuai. Kode booking berjumlah 15 karakter.')
                ->show();
            return;
        }

        // Cari booking di database lokal
        $jknExist = JknRef::selectRaw("referensi_mobilejkn_bpjs.*, poliklinik.nm_poli")
            ->join('maping_poli_bpjs', 'maping_poli_bpjs.kd_poli_bpjs', '=', 'referensi_mobilejkn_bpjs.kodepoli')
            ->join('jadwal', function ($join) {
                $join->on('jadwal.kd_poli', '=', 'maping_poli_bpjs.kd_poli_rs');
                $join->on('jadwal.jam_mulai', '=', DB::raw("CONCAT(LEFT(referensi_mobilejkn_bpjs.jampraktek,5), ':00')"));
                $join->on('jadwal.jam_selesai', '=', DB::raw("CONCAT(RIGHT(referensi_mobilejkn_bpjs.jampraktek,5), ':00')"));
            })
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'jadwal.kd_poli')
            ->where('referensi_mobilejkn_bpjs.nobooking', $this->idNumber)
            ->where('referensi_mobilejkn_bpjs.status', '!=', 'Batal')
            ->first();

        if (!$jknExist) {
            LivewireAlert::warning()
                ->title('Perhatian!')
                ->text('Kode booking tidak ditemukan. Pastikan anda memasukkan sesuai yang tertera pada aplikasi mobile JKN.')
                ->show();
            return;
        }

        $jamMulai = \Carbon\Carbon::parse(substr($jknExist->jampraktek, 0, 5));
        $jamSelesai = \Carbon\Carbon::parse(substr($jknExist->jampraktek, 6, 5));

        if (!now()->isBetween($jamMulai->copy()->addMinutes(-30), $jamSelesai)) {
            LivewireAlert::warning()
                ->title('Perhatian!')
                ->timer(0)
                ->text('Waktu Check In adalah 30 menit sebelum jadwal praktek hingga jam selesai praktek. Poliklinik: ' . $jknExist->nm_poli . '. Jam Praktek: ' . $jknExist->jampraktek . '. Saat ini jam: ' . now()->format('H:i') . '.')
                ->show();
            return;
        }

        // Cari patient berdasarkan nomor rekam medis dari booking JKN
        $this->patient = Patient::where('no_rkm_medis', $jknExist->norm)->first();

        if (!$this->patient) {
            LivewireAlert::error()
                ->title('Pasien Tidak Ditemukan')
                ->text('Data pasien dengan nomor rekam medis ' . $jknExist->norm . ' tidak ditemukan di database.')
                ->timer(0)
                ->show();
            return;
        }

        // Simpan data booking untuk digunakan di proses selanjutnya
        $this->jknBooking = $jknExist;

        // Tampilkan modal dengan lazy load untuk proses BPJS
        $this->showProcessModal = true;
        Flux::modal('processCheckin')->show();
    }

    /**
     * Method untuk proses pengecekan BPJS yang akan dipanggil oleh lazy component
     */
    public function processBpjsCheck()
    {
        try {
            // Gunakan nomor kartu BPJS dari booking JKN
            $identifier = strlen($this->jknBooking->nomorkartu) == 13
                ? $this->jknBooking->nomorkartu
                : $this->jknBooking->nik;

            $bpjsService = new BpjsService($identifier);
            $payload = $bpjsService->getParticipant();

            // Cek apakah request berhasil
            if (!isset($payload['success']) || $payload['success'] === false) {
                $errorCode = $payload['data']['metaData']['code'] ?? 500;
                $errorMessage = $payload['data']['metaData']['message'] ?? 'Koneksi ke server BPJS gagal';

                $this->dispatch('bpjsCheckFailed', [
                    'code' => $errorCode,
                    'message' => $errorMessage
                ]);
                return;
            }

            // Validasi struktur data
            if (!isset($payload['data']['peserta'])) {
                $this->dispatch('bpjsCheckFailed', [
                    'code' => 500,
                    'message' => 'Response dari BPJS tidak memiliki struktur data yang valid'
                ]);
                return;
            }

            $this->participantData = $payload['data'];

            // Tutup modal loading dan tampilkan modal data peserta
            $this->showProcessModal = false;
            Flux::modal('processCheckin')->close();
            Flux::modal('participantData')->show();

            $this->dispatch('setPatient', $this->patient);
            $this->dispatch('setJknBooking', $this->jknBooking);
            $this->dispatch('participantCheck', $this->participantData);
        } catch (BpjsTimeoutException $e) {
            $this->dispatch('bpjsCheckFailed', [
                'code' => 'TIMEOUT',
                'message' => $e->getMessage(),
                'timeout' => true
            ]);
        } catch (\Exception $e) {
            $this->dispatch('bpjsCheckFailed', [
                'code' => 'ERROR',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle ketika pengecekan BPJS gagal
     */
    #[On('bpjsCheckFailed')]
    public function handleBpjsCheckFailed($data)
    {
        $this->showProcessModal = false;
        Flux::modal('processCheckin')->close();

        if (isset($data['timeout']) && $data['timeout']) {
            LivewireAlert::error()
                ->title('Server BPJS Tidak Dapat Diakses')
                ->text($data['message'])
                ->timer(0)
                ->show();

            // $this->redirectRoute('home', navigate: true);
        } else {
            LivewireAlert::error()
                ->title('Gagal Mengambil Data Peserta')
                ->text("Kode: {$data['code']} - {$data['message']}")
                ->timer(0)
                ->show();
        }
    }

    /**
     * Handle antrean poli yang dipilih dari modal JknOrder
     */
    #[On('queueSelected')]
    public function handleQueueSelected(string $noRawat): void
    {
        $this->idNumber = $noRawat;
        $this->checkPatient();
    }

    #[On('numpad-updated')]
    public function checkNumber($payload)
    {
        $this->idNumber = $payload['value'];
    }

    public function nextStep()
    {
        $this->isLoading = true;
        $this->dispatch('setStep', 2);
    }

    #[On('updatePhoneNumber')]
    public function updatePhoneNumber($newPhoneNumber)
    {
        Patient::where('no_rkm_medis', $this->patient->no_rkm_medis)
            ->update(['no_tlp' => $newPhoneNumber]);

        $this->patient['no_tlp'] = $newPhoneNumber;
    }
}
