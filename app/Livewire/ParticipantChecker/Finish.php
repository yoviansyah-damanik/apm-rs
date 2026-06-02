<?php

namespace App\Livewire\ParticipantChecker;

use App\Models\Sep;
use Livewire\Component;
use App\Enums\PurposeOfVisit;
use App\Models\ControlLetter;
use App\Services\BpjsService;
use Livewire\Attributes\Reactive;

class Finish extends Component
{
    #[Reactive]
    public $participantData;

    public $listOfReferences = [];
    public $listOfControlLetters = [];

    public function mount()
    {
        // Validasi participantData tersedia
        if (empty($this->participantData) || !isset($this->participantData['peserta']['noKartu'])) {
            session()->flash('error', 'Data peserta tidak ditemukan. Silakan coba lagi.');
            $this->dispatch('setStep', 1);
            return;
        }

        $this->setReferences();
        $this->setControlLetters();
    }

    public function render()
    {
        return view('livewire.participant-checker.finish');
    }

    public function placeholder()
    {
        return view('placeholders.finish');
    }

    public function setReferences()
    {
        try {
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
                    $tglHabisRujukan = $tglKunjungan->addDays(90);
                    // Tandai rujukan sebagai expired jika hari ini lebih dari 3 bulan sejak tanggal kunjungan
                    $reference['isExpired'] = now()->greaterThanOrEqualTo($tglHabisRujukan->copy()->addDays(1));
                    $reference['expiredAt'] = $tglHabisRujukan->format("Y-m-d");
                    return $reference;
                }, $listOfReferences['data']['fktp']);
            }

            if (!empty($listOfReferences['data']['rs'])) {
                $listOfReferences['data']['rs'] = array_map(function ($reference) {
                    $tglKunjungan = \Carbon\Carbon::parse($reference['tglKunjungan']);
                    $tglHabisRujukan = $tglKunjungan->addDays(90);
                    // Tandai rujukan sebagai expired jika hari ini lebih dari 3 bulan sejak tanggal kunjungan
                    $reference['isExpired'] = now()->greaterThanOrEqualTo($tglHabisRujukan->copy()->addDays(1));
                    $reference['expiredAt'] = $tglHabisRujukan->format("Y-m-d");
                    return $reference;
                }, $listOfReferences['data']['rs']);
            }
            // Simpan list rujukan jika ada data
            $this->listOfReferences = $listOfReferences['data'];
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat mengambil data rujukan: ' . $e->getMessage());
            $this->dispatch('setStep', 1);
        }
    }

    public function setControlLetters()
    {
        try {
            $bpjsService = new BpjsService($this->participantData['peserta']['noKartu']);
            $listOfControlLetters = $bpjsService->getListOfControlLetters();

            $this->listOfControlLetters = $listOfControlLetters['data'];
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengambil data surat kontrol: ' . $e->getMessage());
        }
    }

    public function backToHome()
    {
        return $this->redirectRoute('home', navigate: true);
    }
}
