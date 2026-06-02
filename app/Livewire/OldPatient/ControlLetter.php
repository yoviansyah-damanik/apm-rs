<?php

namespace App\Livewire\OldPatient;

use Carbon\Carbon;
use App\Models\Sep;
use Livewire\Component;
use App\Enums\PurposeOfVisit;
use App\Services\BpjsService;
use Livewire\Attributes\Reactive;
use App\Models\ControlLetter as ControlLetterModel;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class ControlLetter extends Component
{
    #[Reactive]
    public array $participantData;

    #[Reactive]
    public $purposeOfVisit;

    public $listOfControlLetters = [];

    public function mount()
    {
        $this->getListOfControlLetters();
    }

    public function render()
    {
        return view('livewire.old-patient.control-letter');
    }

    public function placeholder()
    {
        return view('placeholders.references');
    }

    public function getListOfControlLetters()
    {
        try {
            $noKartu = $this->participantData['peserta']['noKartu'] ?? null;

            $bpjsService = new BpjsService($noKartu);
            $this->listOfControlLetters = $bpjsService->getListOfControlLetters(
            serviceType: $this->purposeOfVisit->name == PurposeOfVisit::Kontrol->name ? 2 : 1,
            controlType: 2
            )['data'];
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengambil data surat kontrol: ' . $e->getMessage());
            // $this->dispatch('setStep', 1);
        }
    }

    public function selectControlLetter(string $controlLetterData)
    {
        $controlLetter = json_decode(htmlspecialchars_decode($controlLetterData, ENT_QUOTES), true);

        // Check apakah sudah menerbitkan SEP
        $hasSep = isset($controlLetter['terbitSEP']) && $controlLetter['terbitSEP'] === 'Sudah';
        if ($hasSep) {
            session()->flash('error', 'Surat kontrol ini sudah digunakan untuk menerbitkan SEP. Silakan gunakan surat kontrol lain.');
            return;
        }

        // Check apakah tanggal rencana kontrol lebih besar dari hari ini
        $tglRencana = \Carbon\Carbon::parse($controlLetter['tglRencanaKontrol']);
        $today = \Carbon\Carbon::today();
        $isFutureDate = $tglRencana->isAfter($today);

        if ($isFutureDate) {
            session()->flash('error', 'Tanggal kontrol tidak boleh lebih dari hari ini. Silakan pilih surat kontrol lain.');
            return;
        }

        $bpjsService = new BpjsService($this->participantData['peserta']['noKartu']);
        $controlLetter_ = $bpjsService->findControlNumber($controlLetter['noSuratKontrol']);

        // Cek apakah request berhasil dan data reference ada
        if ($controlLetter_['success'] && isset($controlLetter_['data']) && !empty($controlLetter_['data'])) {
            // Kirim reference ke parent component untuk diproses
            $this->dispatch('setReference', $controlLetter_['data']['response']['sep']);
        } else {
            // Tampilkan notifikasi error jika data tidak ditemukan
            $errorMessage = $controlLetter_['message'] ?? 'Gagal mengambil data surat kontrol. Silakan coba lagi.';

            LivewireAlert::title("Data Rujukan Tidak Ditemukan")
                ->text($errorMessage)
                ->withConfirmButton()
                ->confirmButtonText('OK')
                ->timer(0)
                ->error()
                ->show();

            return; // Hentikan proses jika rujukan tidak ditemukan
        }

        // Dispatch event untuk set control letter ke parent component
        $this->dispatch('setControlLetter', $controlLetter_['data']['response']);

        // Lanjut ke step berikutnya
        $this->dispatch('setFormStep');
    }
}
