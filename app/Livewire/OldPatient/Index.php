<?php

namespace App\Livewire\OldPatient;

use App\Enums\PurposeOfVisit as PurposeOfVisitEnum;
use App\Helpers\MagicHelper;
use App\Helpers\SettingHelper;
use App\Models\Patient;
use App\Models\PayType;
use App\Repository\ScheduleRepository;
use App\Services\BpjsService;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    /**
     * Penjelasan variabel $currentStep
     * 1. Cari Pasien
     * 2. Formulir
     * @var int
     */
    public int $currentStep = 1;
    public ?Patient $patient = null;
    public array $participantData = [];
    public array|null $payType = null;
    public array|null $schedule = null;
    public $purposeOfVisit = null;
    public $purposeOfVisits;
    public $payTypes;
    public $registration;
    public bool $registered = false; // Flag untuk cek apakah pasien sudah terdaftar hari ini
    public string $defaultBpjsPayType;
    public array $defaultBpjsPayTypes;
    public $schedules;
    public $reference;
    public $controlLetter;
    // Flag untuk menandai apakah BPJS dapat digunakan (false jika error atau kode 201)
    public bool $canUseBpjs = true;

    private int $maxStep = 6;

    /**
     * Summary of formStep
     * @var int
     * 1. Pilih Jenis Pasien
     * 2. Pilih Tujuan Kunjungan    -   BPJS Kesehatan
     * 3A. Pilih Rujukan            -   BPJS Kesehatan (Tujuan: Rujuk Baru, Rujuk Internal)
     * 3B. Pilih Surat Kontrol      -   BPJS Kesehatan (Tujuan: Kontrol, Kontrol (Post Ranap))
     * 4. Pilih Jadwal              -   Selain BPJS Kesehatan
     * 5. Biometrik                 -   BPJS Kesehatan
     * 6. Elegtabilitas             -   BPJS Kesehatan
     * 7. Konfirmasi                -   Selain BPJS Kesehatan
     * 8. Cetak Antrean (Selesai)   -   Seluruh jenis bayar
     *
     */
    public int $formStep = 1;

    public string $formTitle;
    public string $formSubtitle;

    public function mount()
    {
        $exclude = MagicHelper::parseEnvArray(env('EXCLUDE_PURPOSE_OF_VISIT', '[]'));
        $this->purposeOfVisits = array_values(
            array_filter(PurposeOfVisitEnum::cases(), fn($case) => !in_array($case->name, $exclude))
        );
        $this->payTypes = PayType::whereNotIn('kd_pj', SettingHelper::getExcludePayTypes())
            ->active()
            ->get();
        $this->defaultBpjsPayType = env('DEFAULT_BPJS_PAY_TYPE', 'BPJ');
        $this->defaultBpjsPayTypes = MagicHelper::parseEnvArray(env('DEFAULT_BPJS_PAY_TYPES', '[]'));

        $this->setFormStep($this->formStep);
        $this->setFormTitle($this->formStep);
        // SAMPLE DATA
        // $this->sampleData();

        $this->dispatch('speak', text: 'Selamat datang di Antrean Poli. Silahkan masukkan nomor rekam medis atau nomor identitas anda.');
    }

    public function render()
    {
        return view('livewire.old-patient.index')
            ->layout('components.layouts.console-box');
    }

    public function setFormTitle(int $step)
    {
        switch ($step) {
            case 1:
                $this->formTitle = "Pilih Jenis Bayar";
                $this->formSubtitle = "Silahkan pilih jenis bayar terlebih dahulu.";
                break;
            case 2:
                $this->formTitle = "Pilih Tujuan Kunjungan";
                $this->formSubtitle = "Silahkan pilih tujuan kunjungan anda.";
                break;
            case 3:
                if (
                    $this->payType['kd_pj'] === env('DEFAULT_BPJS_OFFLINE_PAY_TYPE') &&
                    in_array($this->purposeOfVisit->group(), ['RujukPertama', 'Internal'])
                ) {
                    $this->formTitle = "Rujukan Manual";
                    $this->formSubtitle = "Silahkan masukkan rujukan manual anda.";
                } else {
                    if (in_array($this->purposeOfVisit->group(), ['RujukPertama', 'Internal'])) {
                        $this->formTitle = "Pilih Rujukan";
                        $this->formSubtitle = "Silahkan pilih rujukan anda.";
                    } elseif (in_array($this->purposeOfVisit->name, [PurposeOfVisitEnum::Kontrol->name, PurposeOfVisitEnum::KontrolPostRanap->name])) {
                        $this->formTitle = "Pilih Surat Kontrol";
                        $this->formSubtitle = "Silahkan pilih surat kontrol anda.";
                    }
                }
                break;
            case 4:
                $this->formTitle = "Pilih Jadwal";
                $this->formSubtitle = "Silahkan pilih jadwal poli yang ingin anda kunjungi. Pendaftaran Poli dibuka 30 menit sebelum jam mulai.";
                break;
            case 5:
                $this->formTitle = "Biometrik";
                $this->formSubtitle = "Silahkan lakukan validasi biometrik terlebih dahulu. Anda hanya perlu melakukan salah satu validasi biometrik.";
                break;
            case 6:
                $this->formTitle = "Elegtabilitas Peserta";
                $this->formSubtitle = "Silahkan konfirmasi Elegtabilitas Peserta BPJS anda.";
                break;
            case 7:
                $this->formTitle = "Konfirmasi Pendaftaran";
                $this->formSubtitle = "Silahkan konfirmasi pendaftaran anda.";
                break;
            case 8:
                $this->formTitle = "Cetak Antrean";
                $this->formSubtitle = "Silahkan cetak antrean yang telah diterbitkan dan mengunjungi Poli yang anda tuju.";
                break;
        }

        if ($this->currentStep === 2) {
            $this->dispatch('speak', text: "$this->formTitle. $this->formSubtitle");
        }
    }

    public function chronicCheck(Patient $patient)
    {
    }

    #[On('setFormStep')]
    public function setFormStep($step = null)
    {
        if ($step === null) {
            if ($this->formStep + 1 > $this->maxStep) {
                $this->formStep = $this->maxStep;
            } else {
                $this->formStep += 1;
            }
        } else {
            $this->formStep = $step;
        }

        if ($this->payType && !in_array($this->payType['kd_pj'], $this->defaultBpjsPayTypes)) {
            if ($this->formStep == 2) {
                $this->formStep = 4;
                $this->getSchedulesToday();
            } elseif ($this->formStep == 5) {
                $this->formStep = 7;
            }
        } else {
            if ($this->formStep == 3) {
                if (in_array($this->purposeOfVisit->group(), ['RujukPertama', 'Internal'])) {
                } else {
                }
            }
        }

        if ($this->formStep == 4) {
            $this->getSchedulesToday();
        }

        $this->setFormTitle($this->formStep);
    }

    public function prevFormStep(): void
    {
        // Jika pasien sudah terdaftar hari ini dan sedang di halaman finish (formStep 8)
        // Langsung kembali ke halaman search (step 1)
        if ($this->registered && $this->formStep == 8) {
            $this->registered = false; // Reset flag
            $this->registration = null; // Reset registration data
            $this->formStep = 1; // Reset form step
            $this->setStep(1);
            return;
        }

        if ($this->formStep - 1 < 1)
            $this->setStep(1);
        else
            $this->formStep -= 1;

        if ($this->payType && !in_array($this->payType['kd_pj'], $this->defaultBpjsPayTypes)) {
            if ($this->formStep == 2) {
                $this->formStep = 1;
            } else if ($this->formStep == 3) {
                $this->formStep = 1;
            } else if ($this->formStep == 6) {
                $this->formStep = 4;
            }
        } else {
            // dd($this->formStep);
            if ($this->formStep == 3) {
                $this->formStep = 2;
            }
        }

        switch ($this->formStep) {
            case 1:
                $this->setPayType(null);
                break;
            case 2:
                $this->setReference(null);
                $this->setControlLetter(null);
                $this->setPurposeOfVisit(null);
                break;
            case 3:
                $this->setReference(null);
                $this->setControlLetter(null);
                break;
            case 4:
                $this->setSchedule(null);
                break;
        }

        $this->setFormStep($this->formStep);
    }

    public function getSchedulesToday()
    {
        $schedulesRepo = new ScheduleRepository();
        $schedules = $schedulesRepo
            ->getSchedulesToday(true);
        if ($this->controlLetter != null) {
            // Jika ada surat kontrol, filter berdasarkan surat kontrol
            $schedules->checkControlLetter($this->controlLetter);
            // } else if ($this->reference != null && $this->purposeOfVisit->name != PurposeOfVisitEnum::RujukInternal->name) {
        } else if ($this->reference != null) {
            // Jika tidak ada surat kontrol tapi ada rujukan, filter berdasarkan rujukan
            $schedules->checkReference($this->reference);
        }
        // else if ($this->purposeOfVisit->name == PurposeOfVisitEnum::RujukInternal->name) {
        //     $schedules->excludePolyclinics($this->reference['poliRujukan']['kode']);
        // }

        $this->schedules = $schedules
            ->withQuota(isLimitQuota: true)
            ->get();
    }

    #[On('setPatient')]
    public function setPatient(Patient $patient)
    {
        $this->patient = $patient;
    }

    #[On('setStep')]
    public function setStep(int $step)
    {
        $this->currentStep = $step;
        $this->setFormTitle($this->formStep);
    }

    #[On('setPayType')]
    public function setPayType(string|array|null $payType)
    {
        // Jika string (legacy), convert ke format array
        if (is_string($payType)) {
            $payTypeModel = $this->payTypes->firstWhere('kd_pj', $payType);
            $this->payType = [
                'kd_pj' => $payType,
                'png_jawab' => $payTypeModel?->png_jawab ?? $payType
            ];
        } else {
            $this->payType = $payType;
        }
    }

    #[On('setSchedule')]
    public function setSchedule(array|null $schedule)
    {
        $this->schedule = $schedule;
    }

    #[On('setParticipantData')]
    public function setParticipantData(array $participantData)
    {
        $this->participantData = $participantData;
    }

    #[On('setPurposeOfVisit')]
    public function setPurposeOfVisit(string|null $purposeOfVisit)
    {
        $this->purposeOfVisit = $purposeOfVisit != null ? PurposeOfVisitEnum::{$purposeOfVisit} : null;
    }

    #[On('setRegistration')]
    public function setRegistration(array $registration)
    {
        $this->registration = $registration;
        $this->registered = true; // Tandai bahwa pasien sudah terdaftar hari ini
    }

    #[On('setCanUseBpjs')]
    public function setCanUseBpjs(bool $canUseBpjs)
    {
        $this->canUseBpjs = $canUseBpjs;
    }

    #[On('setReference')]
    public function setReference(array|null $reference)
    {
        $this->reference = $reference;
    }

    #[On('setControlLetter')]
    public function setControlLetter(array|null $controlLetter)
    {
        $this->controlLetter = $controlLetter;
    }
}
