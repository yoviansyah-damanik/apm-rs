<?php

namespace App\Livewire\OldPatient;

use App\Models\Icd10;
use App\Services\BpjsService;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class BpjsOffline extends Component
{
    #[Reactive]
    public array $participantData;

    public string $noRujukan = '';
    public string $tglRujukan = '';
    public string $asalFaskesKode = '';
    public string $asalFaskesNama = '';
    public string $asalFaskesJenis = ''; // 'FKTP' | 'RS'
    public string $diagnosisKode = '';
    public string $diagnosisNama = '';

    // Modal pencarian faskes
    public string $faskesQuery = '';
    public array $faskesResults = [];
    public string $faskesError = '';

    // Modal pencarian diagnosis
    public string $diagnosisQuery = '';
    public array $diagnosisResults = [];
    public string $diagnosisError = '';

    public function render()
    {
        return view('livewire.old-patient.bpjs-offline');
    }

    public function searchFaskes(): void
    {
        $this->faskesError = '';
        $this->faskesResults = [];

        if (strlen(trim($this->faskesQuery)) < 3) {
            $this->faskesError = 'Masukkan minimal 3 karakter untuk mencari.';
            return;
        }

        $bpjsService = new BpjsService($this->participantData['peserta']['noKartu']);
        $keyword = trim($this->faskesQuery);

        $resultFktp = $bpjsService->getReferensiFaskes($keyword, 1);
        $resultRs = $bpjsService->getReferensiFaskes($keyword, 2);

        $combined = [];

        if ($resultFktp['success']) {
            foreach ($resultFktp['data'] as $item) {
                $combined[] = [...$item, 'jenis' => 'FKTP'];
            }
        }

        if ($resultRs['success']) {
            foreach ($resultRs['data'] as $item) {
                $combined[] = [...$item, 'jenis' => 'RS'];
            }
        }

        $this->faskesResults = $combined;

        if (empty($combined)) {
            $this->faskesError = 'Faskes tidak ditemukan. Coba kata kunci lain.';
        }
    }

    public function selectFaskes(string $kode, string $nama, string $jenis): void
    {
        $this->asalFaskesKode = $kode;
        $this->asalFaskesNama = $nama;
        $this->asalFaskesJenis = $jenis;

        $this->faskesQuery = '';
        $this->faskesResults = [];
        $this->faskesError = '';

        $this->dispatch('close-faskes-modal');
    }

    public function searchDiagnosis(): void
    {
        $this->diagnosisError = '';
        $this->diagnosisResults = [];

        if (strlen(trim($this->diagnosisQuery)) < 2) {
            $this->diagnosisError = 'Masukkan minimal 2 karakter untuk mencari.';
            return;
        }

        $results = Icd10::active()
            ->search(trim($this->diagnosisQuery))
            ->orderBy('kd_penyakit')
            ->limit(30)
            ->get(['kd_penyakit', 'nm_penyakit'])
            ->toArray();

        $this->diagnosisResults = $results;

        if (empty($results)) {
            $this->diagnosisError = 'Diagnosis tidak ditemukan. Coba kata kunci lain.';
        }
    }

    public function selectDiagnosis(string $kode, string $nama): void
    {
        $this->diagnosisKode = $kode;
        $this->diagnosisNama = $nama;

        $this->diagnosisQuery = '';
        $this->diagnosisResults = [];
        $this->diagnosisError = '';

        $this->dispatch('close-diagnosis-modal');
    }

    public function submit(): void
    {
        $this->validate([
            'noRujukan' => 'required|min:3',
            'tglRujukan' => 'required|date',
            'asalFaskesKode' => 'required',
        ], [
            'noRujukan.required' => 'Nomor rujukan wajib diisi.',
            'noRujukan.min' => 'Nomor rujukan minimal 3 karakter.',
            'tglRujukan.required' => 'Tanggal rujukan wajib diisi.',
            'tglRujukan.date' => 'Format tanggal tidak valid.',
            'asalFaskesKode.required' => 'Asal faskes wajib dipilih.',
        ]);

        $reference = [
            'noKunjungan' => $this->noRujukan,
            'noRujukan' => $this->noRujukan,
            'tglKunjungan' => \Carbon\Carbon::parse($this->tglRujukan)->format('Y-m-d'),
            'poliRujukan' => ['kode' => '', 'nama' => ''],
            'diagnosa' => ['kode' => $this->diagnosisKode, 'nama' => $this->diagnosisNama ?: '-'],
            'provPerujuk' => ['kode' => $this->asalFaskesKode, 'nama' => $this->asalFaskesNama],
            'isExpired' => false,
            'hasUsed' => false,
            'expiredAt' => null,
            'isManual' => true,
            'type' => $this->asalFaskesJenis === 'RS' ? 'rs' : 'fktp',
        ];

        $this->dispatch('setReference', $reference);
        $this->dispatch('setFormStep');
    }
}
