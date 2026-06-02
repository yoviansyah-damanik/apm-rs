<?php

namespace App\Livewire\CheckIn;

use App\Models\JknRef;
use Livewire\Component;
use App\Models\Register;
use Illuminate\View\View;
use Livewire\Attributes\On;
use App\Enums\PurposeOfVisit;
use App\Models\ControlLetter;
use App\Services\BpjsService;
use Livewire\Attributes\Reactive;
use App\Http\Controllers\Controller;
use App\Repository\ScheduleRepository;

class Elegtability extends Component
{
    public $participantData;
    #[Reactive]
    public $patient;
    public $jknBooking;
    public $defaultBpjsPayType;
    public $reference;
    public $schedule;
    public $controlLetter;
    public $purposeOfVisit;
    public $controlNumber;
    public $referenceId;
    public $registration;

    public function mount()
    {
        $this->registration = $this->setRegistration();
        $this->purposeOfVisit = $this->setPurposeOfVisit();

        if (!in_array($this->purposeOfVisit->name, [PurposeOfVisit::Kontrol->name, PurposeOfVisit::KontrolPostRanap->name])) {
            $this->referenceId = JknRef::where('no_rawat', $this->registration['no_rawat'])
                ->first()->nomorreferensi;
            $this->reference = $this->setReference()['data'];
        } else {
            $this->controlLetter = $this->setControlLetter();
            $this->referenceId = $this->controlLetter['sep']['provPerujuk']['noRujukan'];
            $this->controlNumber = $this->controlLetter['noSuratKontrol'];
            $this->reference = $this->setReference()['data'];
        }

        $this->schedule = $this->setSchedule();
    }

    public function render(): View
    {
        return view('livewire.check-in.elegtability');
    }

    public function setRegistration()
    {
        return Register::where('no_rawat', $this->jknBooking['no_rawat'])->first()->toArray();
    }

    public function setReference()
    {
        $bpjsService = new BpjsService($this->participantData['peserta']['noKartu']);
        return $bpjsService->getListOfReferences($this->referenceId);
    }

    // Ambil jadwal dari repo dan sesuaikan dengan tujukan kunjungan
    public function setSchedule()
    {
        $schedulesRepo = new ScheduleRepository();
        $schedules = $schedulesRepo->getSchedulesToday()
            ->withQuota(isLimitQuota: false)
            ->get()
            ->where('kd_dokter', $this->registration['kd_dokter'])
            ->where('kd_poli', $this->registration['kd_poli'])
            ->first();

        return $schedules;
    }

    public function setControlLetter()
    {
        $bpjsService = new BpjsService($this->participantData['peserta']['noKartu']);
        $controlNumber = JknRef::where('no_rawat', $this->registration['no_rawat'])
            ->first()->nomorreferensi;

        $controlLetter = $bpjsService->findControlNumber($controlNumber)['data']['response'];

        $this->purposeOfVisit = $controlLetter['sep']['jnsPelayanan'] == 'Rawat Inap' ? PurposeOfVisit::KontrolPostRanap : PurposeOfVisit::Kontrol;

        return $controlLetter;
    }

    public function setPurposeOfVisit()
    {
        $jenisKunjungan = substr($this->jknBooking['jeniskunjungan'], 0, 1);
        $purposeOfVisit = '';
        if ($jenisKunjungan == '1' || $jenisKunjungan == '4') {
            $purposeOfVisit = PurposeOfVisit::RujukPertama;
        } else if ($jenisKunjungan == '2') {
            $purposeOfVisit = PurposeOfVisit::RujukInternal;
        } else if ($jenisKunjungan == '3') {
            // Cari kondisi untuk membedakan Kontrol dan KontrolPostRanap
            if ($jenisKunjungan) {
                $purposeOfVisit = PurposeOfVisit::Kontrol;
            } else {
                $purposeOfVisit = PurposeOfVisit::KontrolPostRanap;
            }
        }

        return $purposeOfVisit;
    }

    #[On('elegtabilityData')]
    public function elegtabilityData($payload)
    {
        if ($payload['status'] === true) {
            $this->dispatch('setFormStep');
        }
    }
}
