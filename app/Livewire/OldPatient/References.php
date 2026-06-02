<?php

namespace App\Livewire\OldPatient;

use App\Models\Sep;
use App\Services\BpjsService;
use Livewire\Component;
use Livewire\Attributes\Reactive;

class References extends Component
{
    #[Reactive]
    public array $participantData;

    public $listOfReferences;

    public $isInternal = false;

    public function mount()
    {
        $this->getListOfReferences();
    }

    public function render()
    {
        return view('livewire.old-patient.references');
    }

    public function placeholder()
    {
        return view('placeholders.references');
    }

    public function getListOfReferences()
    {
        $bpjsService = new BpjsService($this->participantData['peserta']['noKartu']);
        $listOfReferences = $bpjsService->getListOfReferences();

        // Cek jika penarikan data tidak sukses
        if (!isset($listOfReferences['success']) || $listOfReferences['success'] !== true) {
            session()->flash('error', 'Gagal mengambil data rujukan. Silakan coba lagi.');
            $this->dispatch('setStep', 1); // Kembali ke form awal (pengisian nomor identitas)
            return;
        }

        // Cek jika data FKTP dan RS kosong
        $fktpEmpty = empty($listOfReferences['data']['fktp']);
        $rsEmpty = empty($listOfReferences['data']['rs']);

        if ($fktpEmpty && $rsEmpty) {
            session()->flash('error', 'Tidak ditemukan data rujukan FKTP maupun RS untuk nomor peserta ini.');
            // $this->dispatch('setStep', 1); // Kembali ke form awal (pengisian nomor identitas)
            return;
        }

        // Filter dan tandai rujukan yang expired (lebih dari 3 bulan)
        if (!empty($listOfReferences['data']['fktp'])) {
            $listOfReferences['data']['fktp'] = array_map(function ($reference) {
                $tglKunjungan = \Carbon\Carbon::parse($reference['tglKunjungan']);
                $tglHabisRujukan = $tglKunjungan->copy()->addDays(90);
                // Tandai rujukan sebagai expired jika hari ini lebih dari 3 bulan sejak tanggal kunjungan
                $reference['isExpired'] = now()->greaterThanOrEqualTo($tglHabisRujukan->copy()->addDays(1));
                $reference['expiredAt'] = $tglHabisRujukan->format("d F Y");
                $reference['hasUsed'] = $this->isInternal ? false : Sep::where('no_rujukan', $reference['noKunjungan'])->exists();
                return $reference;
            }, $listOfReferences['data']['fktp']);
        }

        if (!empty($listOfReferences['data']['rs'])) {
            $listOfReferences['data']['rs'] = array_map(function ($reference) {
                $tglKunjungan = \Carbon\Carbon::parse($reference['tglKunjungan']);
                $tglHabisRujukan = $tglKunjungan->copy()->addDays(90);
                // Tandai rujukan sebagai expired jika hari ini lebih dari 3 bulan sejak tanggal kunjungan
                $reference['isExpired'] = now()->greaterThanOrEqualTo($tglHabisRujukan->copy()->addDays(1));
                $reference['expiredAt'] = $tglHabisRujukan->format("d F Y");
                $reference['hasUsed'] = $this->isInternal ? false : Sep::where('no_rujukan', $reference['noKunjungan'])->exists();
                return $reference;
            }, $listOfReferences['data']['rs']);
        }
        // Simpan list rujukan jika ada data
        $this->listOfReferences = $listOfReferences['data'];
    }

    public function selectReference(string $referenceData, string $type)
    {
        // Decode JSON data rujukan yang dipilih
        $reference = json_decode(htmlspecialchars_decode($referenceData, ENT_QUOTES), true);

        // Validasi: Cek apakah rujukan sudah kadaluarsa (lebih dari 3 bulan)
        if (isset($reference['isExpired']) && $reference['isExpired'] === true) {
            session()->flash('error', 'Rujukan ini sudah kadaluarsa (lebih dari 3 bulan). Silakan gunakan rujukan yang masih berlaku.');
            return;
        }

        // Tambahkan informasi tipe rujukan (FKTP atau RS)
        $reference['type'] = $type;

        // Dispatch event untuk set reference ke parent component
        $this->dispatch('setReference', $reference);

        // Lanjut ke step berikutnya
        $this->dispatch('setFormStep');
    }
}
