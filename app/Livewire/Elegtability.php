<?php

namespace App\Livewire;

use App\Enums\PurposeOfVisit;
use App\Helpers\SettingHelper;
use App\Models\BpjsDoctor;
use App\Models\BpjsPolyclinic;
use App\Models\ControlLetter;
use App\Models\Register;
use App\Models\JknRef;
use App\Models\PatientDiagnose;
use App\Models\Sep;
use App\Models\SepInternal;
use App\Services\BpjsService;
use App\Services\RegisterService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;

class Elegtability extends Component
{
    // TARIK
    public $reference;
    public $controlLetter;
    public $payType;
    public array $participantData;
    public $patient;
    public $schedule;
    public $purposeOfVisit;
    // END TARIK
    public $patientName;
    public $mrNumber;
    public $phoneNumber;
    public $nik;
    public $participantNumber;
    public $birthDate;
    public $refOriginId;
    public $refOrigin;
    public $refNumber;
    public $refOriginVisit;
    public $refDate;
    public $sepDate;
    public $servicePPKId;
    public $servicePPK;
    public $doctorId;
    public $doctorName;
    public $polyclinicId;
    public $polyclinicName;
    public $serviceType;
    public $classRights;
    public $classRightsName;
    public $note;
    public $serviceAssessment;
    public $procedureFlag;
    public $support;
    public $executive = 0;
    public $cob = 0;
    public $cataract = 0;
    public $controlNumber;
    public $user = 'APM';
    public BpjsDoctor $bpjsDoctor;
    public BpjsPolyclinic $bpjsPolyclinic;
    public bool $isOriginValid = true;
    public $diagnose;
    public $diagnoseId;

    public $status = '1'; //  1. Onsite, 2. JKN

    public function mount(): void
    {
        $this->bpjsDoctor = BpjsDoctor::where('kd_dokter', $this->schedule['doctor']['kd_dokter'])
            ->first();
        $this->bpjsPolyclinic = BpjsPolyclinic::where('kd_poli_rs', $this->schedule['polyclinic']['kd_poli'])
            ->first();

        // SET PASIEN
        $this->setPatientData();
        $this->checkSep();

    }

    public function checkSep()
    {
        $existSep = Sep::where([
            ['tglsep', now()->format('Y-m-d')],
            ['nomr', $this->mrNumber]
        ])
            ->exists();

        if ($existSep) {
            $this->dispatch('setFormStep');
        } else {
            // SET RUJUKAN
            $this->setReferalData();
            // SET SEP
            $this->setSepData();
        }
    }

    public function render()
    {
        return view('livewire.elegtability');
    }

    public function placeholder()
    {
        return view('placeholders.elegtability');
    }

    public function setPatientData(): void
    {
        $this->patientName = $this->participantData['peserta']['nama'];
        $this->mrNumber = $this->patient['no_rkm_medis'];
        $this->phoneNumber = $this->patient['no_tlp'];
        $this->birthDate = $this->participantData['peserta']['tglLahir'];
        $this->nik = $this->participantData['peserta']['nik'];
        $this->participantNumber = $this->participantData['peserta']['noKartu'];
    }

    public function setReferalData(): void
    {
        $this->refOrigin = '';
        $this->refOriginId = '';
        $this->refNumber = '';
        $this->refDate = '';
        $this->controlNumber = '';
        $this->diagnose = '';
        $this->diagnoseId = '';


        $bpjsService = new BpjsService($this->participantData['peserta']['noKartu']);

        // Perlu ditambahkan validasi, apabila tanggal rencana kontrol pada surat kontrol di SIMRS lebih kecil daripada rencana kontrol di service
        // otomatis SIMRS hanya perlu melakukan update data tanpa mengirim ulang ke service bpjs
        if (in_array($this->purposeOfVisit->name, [PurposeOfVisit::Kontrol->name, PurposeOfVisit::KontrolPostRanap->name])) {
            $existControl = ControlLetter::firstOrCreate([
                'no_surat' => $this->controlLetter['noSuratKontrol'],
            ], [
                'no_sep' => $this->controlLetter['sep']['noSep'],
                'tgl_surat' => $this->controlLetter['tglTerbit'],
                'tgl_rencana' => $this->controlLetter['tglRencanaKontrol'],
                'kd_dokter_bpjs' => $this->controlLetter['kodeDokter'],
                'nm_dokter_bpjs' => $this->controlLetter['namaDokter'],
                'kd_poli_bpjs' => $this->controlLetter['poliTujuan'],
                'nm_poli_bpjs' => $this->controlLetter['namaPoliTujuan'],
            ]);

            if (
                $existControl['tgl_rencana'] != now()->setTimezone('Asia/Jakarta')->format('Y-m-d')
                || $existControl['kd_dokter_bpjs'] != $this->bpjsDoctor->kd_dokter_bpjs
                || $existControl['kd_poli_bpjs'] != $this->bpjsPolyclinic->kd_poli_bpjs
            ) {
                $updateControl = $bpjsService->updateControlNumber(
                    $this->controlLetter['noSuratKontrol'],
                    $this->controlLetter['sep']['noSep'],
                    $this->bpjsDoctor->kd_dokter_bpjs,
                    $this->bpjsPolyclinic->kd_poli_bpjs,
                    now()->setTimezone('Asia/Jakarta')->format('Y-m-d'),
                );

                if (isset($updateControl['success']) && $updateControl['success'] === false) {
                    $errorMessage = $updateControl['data']['metaData']['message'] ?? 'Koneksi ke server BPJS gagal';
                    LivewireAlert::error()
                        ->title("Terjadi kesalahan saat memperbarui tanggal kontrol.")
                        ->text($errorMessage)
                        ->timer(0)
                        ->show();
                    $this->isOriginValid = false;
                    return;
                } else {
                    $existControl->update([
                        'tgl_rencana' => now()->format('Y-m-d'),
                        'kd_dokter_bpjs' => $this->bpjsDoctor->kd_dokter_bpjs,
                        'nm_dokter_bpjs' => $this->bpjsDoctor->nm_dokter_bpjs,
                        'kd_poli_bpjs' => $this->bpjsPolyclinic->kd_poli_bpjs,
                        'nm_poli_bpjs' => $this->bpjsPolyclinic->nm_poli_bpjs,
                    ]);

                    LivewireAlert::success()
                        ->title("Status Surat Kontrol")
                        ->text("Surat kontrol anda berhasil diperbarui.")
                        ->show();
                }
            }

            $this->controlNumber = $this->controlLetter['noSuratKontrol'];

            $this->refOriginId = $this->controlLetter['sep']['provPerujuk']['kdProviderPerujuk'];
            $this->refOrigin = $this->controlLetter['sep']['provPerujuk']['nmProviderPerujuk'];
            // $this->refOriginVisit = Str::containsAll($this->refOriginId, ['R', 'S'], ignoreCase: true) ? 2 : 1;
            $this->refOriginVisit = $this->controlLetter['sep']['provPerujuk']['asalRujukan'];
            if ($this->purposeOfVisit->name == PurposeOfVisit::Kontrol->name) {
                $this->refNumber = $this->controlLetter['sep']['provPerujuk']['noRujukan'];
                $this->refDate = $this->controlLetter['sep']['provPerujuk']['tglRujukan'];
                $this->diagnoseId = 'Z09.8';
                $this->diagnose = 'Follow-up examination after other treatment for other conditions';
            } else {
                $this->refNumber = $this->controlLetter['sep']['noSep'];
                $this->refDate = $this->controlLetter['sep']['tglSep'];
                $this->diagnoseId = 'Z09.9';
                $this->diagnose = 'Follow-up examination after unspecified treatment for other conditions';
            }
        } else {
            $this->refNumber = $this->reference['noKunjungan'];
            $this->refOriginId = $this->reference['provPerujuk']['kode'];
            $this->refOrigin = $this->reference['provPerujuk']['nama'];
            $this->refDate = $this->reference['tglKunjungan'];
            $this->diagnoseId = $this->reference['diagnosa']['kode'];
            $this->diagnose = $this->reference['diagnosa']['nama'];
            $this->refOriginVisit = Str::contains($this->refOriginId, ['R', 'S'], ignoreCase: true) ? 2 : 1;
        }
    }

    public function backToSchedule(): void
    {
        $this->dispatch('setFormStep', 3);
    }

    public function setSepData(): void
    {
        // DOKTER
        $this->doctorId = $this->bpjsDoctor->kd_dokter_bpjs;
        $this->doctorName = $this->bpjsDoctor->nm_dokter_bpjs;

        // POLI
        $this->polyclinicId = $this->bpjsPolyclinic->kd_poli_bpjs;
        $this->polyclinicName = $this->bpjsPolyclinic->nm_poli_bpjs;

        // SEP
        $this->sepDate = now()->setTimezone('Asia/Jakarta')->format('Y-m-d');

        $this->servicePPKId = SettingHelper::get('hospitalBpjsCode');
        $this->servicePPK = SettingHelper::get('hospitalName');
        $this->serviceType = 2; // 1. Ranap 2. Ralan
        $this->classRights = $this->participantData['peserta']['hakKelas']['kode'];
        $this->note = $this->getSepData($this->purposeOfVisit->name)['note'];
        $this->serviceAssessment = $this->getSepData($this->purposeOfVisit->name)['serviceAssessment']['code'];
        $this->procedureFlag = $this->getSepData($this->purposeOfVisit->name)['flagProcedure']['code'];
        $this->support = $this->getSepData($this->purposeOfVisit->name)['support']['code'];
        $this->executive = 0;
        $this->cob = 0;
    }

    /* "tujuanKunj":        {"0": Normal,
     *                       "1": Prosedur,
     *                       "2": Konsul Dokter},
     * "flagProcedure":      {"0": Prosedur Tidak Berkelanjutan,
     *                       "1": Prosedur dan Terapi Berkelanjutan} ==> diisi "" jika tujuanKunj = "0",
     * "kdPenunjang":        {"1": Radioterapi,
     *                       "2": Kemoterapi,
     *                       "3": Rehabilitasi Medik,
     *                       "4": Rehabilitasi Psikososial,
     *                       "5": Transfusi Darah,
     *                       "6": Pelayanan Gigi,
     *                       "7": Laboratorium,
     *                       "8": USG,
     *                       "9": Farmasi,
     *                       "10": Lain-Lain,
     *                       "11": MRI,
     *                       "12": HEMODIALISA} ==> diisi "" jika tujuanKunj = "0",
     * "assesmentPel":       {"1": Poli spesialis tidak tersedia pada hari sebelumnya,
     *                       "2": Jam Poli telah berakhir pada hari sebelumnya,
     *                       "3": Dokter Spesialis yang dimaksud tidak praktek pada hari sebelumnya,
     *                       "4": Atas Instruksi RS} ==> diisi jika tujuanKunj = "2" atau "0" (politujuan beda dengan poli rujukan dan hari beda),
     *                       "5": Tujuan Kontrol,
     */
    public function getSepData($purposeOfVisit)
    {
        if ($this->payType == env('DEFAULT_BPJS_OFFLINE_PAY_TYPE')) {
            return [
                'purposeOfVisit' => [
                    'code' => 0,
                    'title' => 'Normal',
                ],
                'flagProcedure' => [
                    'code' => '',
                    'title' => '-'
                ],
                'support' => [
                    'code' => '',
                    'title' => '-'
                ],
                'serviceAssessment' => [
                    'code' => '',
                    'title' => '-'
                ],
                'note' => 'FKTP Non Jarkomdat'
            ];
        }

        $result = [
            'RujukPertama' => [
                'purposeOfVisit' => [
                    'code' => 0,
                    'title' => 'Normal',
                ],
                'flagProcedure' => [
                    'code' => '',
                    'title' => '-'
                ],
                'support' => [
                    'code' => '',
                    'title' => '-'
                ],
                'serviceAssessment' => [
                    'code' => '',
                    'title' => '-'
                ],
                'note' => 'Rujukan Pertama'
            ],
            'Kontrol' => [
                'purposeOfVisit' => [
                    'code' => 2,
                    'title' => 'Konsul Dokter',
                ],
                'flagProcedure' => [
                    'code' => '',
                    'title' => '-'
                ],
                'support' => [
                    'code' => '',
                    'title' => '-'
                ],
                'serviceAssessment' => [
                    'code' => '5',
                    'title' => 'Tujuan Kontrol'
                ],
                'note' => 'Kontrol'
            ],
            'KontrolPostRanap' => [
                'purposeOfVisit' => [
                    'code' => 0,
                    'title' => 'Normal',
                ],
                'flagProcedure' => [
                    'code' => '',
                    'title' => '-'
                ],
                'support' => [
                    'code' => '',
                    'title' => '-'
                ],
                'serviceAssessment' => [
                    'code' => '',
                    'title' => '-'
                ],
                'note' => 'Kontrol Post Ranap'
            ],
            'RujukInternal' => [
                'purposeOfVisit' => [
                    'code' => 0,
                    'title' => 'Normal',
                ],
                'flagProcedure' => [
                    'code' => '',
                    'title' => '-'
                ],
                'support' => [
                    'code' => '',
                    'title' => '-'
                ],
                'serviceAssessment' => [
                    'code' => 4,
                    'title' => 'Atas Instruksi RS'
                ],
                'note' => 'Rujukan Internal'
            ],
        ];

        return $result[$purposeOfVisit];
    }

    public function process()
    {
        if (empty($this->checkAvailableSEP())) {
            if ($this->status == 1) {
                $this->exeOnsite();
            } else {
                $this->exeJKN();
            }
        }
    }

    public function checkAvailableSEP(): array
    {
        if (empty($this->refNumber)) {
            return [];
        }

        try {
            $bpjsService = (new BpjsService($this->participantNumber))->setService('vclaim');
            $lastSep = $bpjsService->getLastSEPByRujukan($this->refNumber);

            if (isset($lastSep['success']) && $lastSep['success'] === false || !isset($lastSep['data']['response'])) {
                return [];
            }

            $sepData = $lastSep['data']['response'];

            if (($sepData['tglSep'] ?? '') != now()->format('Y-m-d')) {
                return [];
            }

            // SEP sudah terbit hari ini berdasarkan rujukan ini
            DB::connection('simrs')->beginTransaction();

            $referralData = [
                'refOrigin' => $this->refOrigin,
                'refOriginId' => $this->refOriginId,
                'refOriginVisit' => $this->refOriginVisit,
                'refNumber' => $this->refNumber,
                'refDate' => $this->refDate,
                'controlNumber' => $this->controlNumber,
                'diagnose' => $this->diagnose,
                'diagnoseId' => $this->diagnoseId,
                'serviceType' => $this->serviceType,
                'classRights' => $this->classRights,
                'note' => $this->note,
                'serviceAssessment' => $this->serviceAssessment,
                'procedureFlag' => $this->procedureFlag,
                'support' => $this->support,
                'executive' => $this->executive,
                'cob' => $this->cob,
                'cataract' => $this->cataract,
            ];

            $kelamin = \in_array($sepData['peserta']['kelamin'], ['P', 'L'])
                ? $sepData['peserta']['kelamin']
                : ($sepData['peserta']['kelamin'] === 'Perempuan' ? 'P' : 'L');

            $sepFields = [
                'tglsep' => $sepData['tglSep'],
                'tglrujukan' => $this->refDate,
                'no_rujukan' => $sepData['noRujukan'],
                'kdppkrujukan' => $this->refOriginId,
                'nmppkrujukan' => $this->refOrigin,
                'kdppkpelayanan' => SettingHelper::get('hospitalBpjsCode'),
                'nmppkpelayanan' => SettingHelper::get('hospitalName'),
                'jnspelayanan' => $this->serviceType,
                'catatan' => $sepData['catatan'],
                'diagawal' => $this->diagnoseId,
                'nmdiagnosaawal' => $this->diagnose,
                'kdpolitujuan' => $this->polyclinicId,
                'nmpolitujuan' => $this->polyclinicName,
                'klsrawat' => $this->classRights,
                'klsnaik' => '',
                'pembiayaan' => '',
                'pjnaikkelas' => '',
                'lakalantas' => '0',
                'user' => 'APM',
                'nomr' => $sepData['peserta']['noMr'],
                'nama_pasien' => $sepData['peserta']['nama'],
                'tanggal_lahir' => $sepData['peserta']['tglLahir'],
                'peserta' => $sepData['peserta']['jnsPeserta'],
                'jkel' => $kelamin,
                'no_kartu' => $sepData['peserta']['noKartu'],
                'tglpulang' => null,
                'asal_rujukan' => $this->refOriginVisit == 2 ? '2. Faskes 2(RS)' : '1. Faskes 1',
                'eksekutif' => $this->executive ? '1.Ya' : '0. Tidak',
                'cob' => $this->cob ? '1.Ya' : '0. Tidak',
                'notelep' => $this->participantData['peserta']['mr']['noTelepon'] ?? $this->patient['no_tlp'],
                'katarak' => $this->cataract ? '1.Ya' : '0. Tidak',
                'tglkkl' => null,
                'keterangankkl' => '',
                'suplesi' => '0. Tidak',
                'no_sep_suplesi' => '',
                'kdprop' => '',
                'nmprop' => '',
                'kdkab' => '',
                'nmkab' => '',
                'kdkec' => '',
                'nmkec' => '',
                'noskdp' => $this->controlNumber,
                'kddpjp' => $this->doctorId,
                'nmdpdjp' => $this->doctorName,
                'tujuankunjungan' => $sepData['tujuanKunj'],
                'flagprosedur' => $sepData['flagProcedure'],
                'penunjang' => $sepData['kdPenunjang'],
                'asesmenpelayanan' => $sepData['assestmenPel'],
                'kddpjplayanan' => $this->doctorId,
                'nmdpjplayanan' => $this->doctorName,
                'backdate' => false,
                'antrean' => true,
            ];

            $register = null;
            if ($this->status == 1) {
                $registerService = new RegisterService();
                $register = $registerService->insert(
                    $this->patient,
                    $referralData,
                    [
                        'polyclinicId' => $this->schedule['polyclinic']['kd_poli'],
                        'polyclinicName' => $this->schedule['polyclinic']['nm_poli'],
                    ],
                    [
                        'doctorId' => $this->schedule['doctor']['kd_dokter'],
                        'doctorName' => $this->schedule['doctor']['nm_dokter'],
                    ],
                    $this->payType,
                    false
                );
                $noRawat = $register['no_rawat'];
            } else {
                $noRawat = JknRef::where('nomorkartu', $this->participantNumber)
                    ->where('tanggalperiksa', now()->setTimezone('Asia/Jakarta')->format('Y-m-d'))
                    ->where('status', '<>', 'Batal')
                    ->first()->no_rawat;
            }

            $sepModel = $this->purposeOfVisit->name === PurposeOfVisit::KonsulInternal->name
                ? SepInternal::class
                : Sep::class;

            $sep = $sepModel::firstOrCreate(
                ['no_sep' => $sepData['noSep']],
                array_merge($sepFields, ['no_rawat' => $noRawat])
            );

            $this->addDiagnose($noRawat);

            DB::connection('simrs')->commit();

            if ($this->status == 1) {
                $this->dispatch('elegtabilityData', [
                    'status' => true,
                    'registration' => $register,
                    'sep' => $sep,
                ]);
            } else {
                $this->dispatch('elegtabilityData', [
                    'status' => true,
                    'sep' => $sep,
                ]);
            }

            return ['handled' => true];

        } catch (\Exception $e) {
            DB::connection('simrs')->rollBack();
            LivewireAlert::error()
                ->title('Error')
                ->text($e->getMessage())
                ->timer(0)
                ->show();
            return ['error' => true];
        }
    }

    public function exeOnsite()
    {
        DB::connection('simrs')->beginTransaction();
        try {
            $referralData = [
                'refOrigin' => $this->refOrigin,
                'refOriginId' => $this->refOriginId,
                'refOriginVisit' => $this->refOriginVisit,
                'refNumber' => $this->refNumber,
                'refDate' => $this->refDate,
                'controlNumber' => $this->controlNumber,
                'diagnose' => $this->diagnose,
                'diagnoseId' => $this->diagnoseId,
                'serviceType' => $this->serviceType,
                'classRights' => $this->classRights,
                'note' => $this->note,
                'serviceAssessment' => $this->serviceAssessment,
                'procedureFlag' => $this->procedureFlag,
                'support' => $this->support,
                'executive' => $this->executive,
                'cob' => $this->cob,
                'cataract' => $this->cataract,
            ];

            $registerService = new RegisterService();
            $register = $registerService->insert(
                $this->patient,
                $referralData,
                [
                    'polyclinicId' => $this->schedule['polyclinic']['kd_poli'],
                    'polyclinicName' => $this->schedule['polyclinic']['nm_poli'],
                ],
                [
                    'doctorId' => $this->schedule['doctor']['kd_dokter'],
                    'doctorName' => $this->schedule['doctor']['nm_dokter'],
                ],
                $this->payType,
                false
            );

            $bpjsService = new BpjsService($this->participantNumber);
            if ($this->payType != env('DEFAULT_BPJS_OFFLINE_PAY_TYPE')) {
                // Ambilkan Antrean
                $bpjsService = $bpjsService->setService('antrol');
                $addAntrol = $bpjsService->addQueue(
                    patient: $this->patient,
                    participantData: $this->participantData,
                    register: $register,
                    doctor: [
                        'doctorId' => $this->doctorId,
                        'doctorName' => $this->doctorName,
                    ],
                    polyclinic: [
                        'polyclinicId' => $this->polyclinicId,
                        'polyclinicName' => $this->polyclinicName,
                    ],
                    schedule: $this->schedule,
                    referralData: $referralData,
                    purposeOfVisit: $this->purposeOfVisit
                );
                // dd($addAntrol);

                // Check antrol response
                if (!isset($addAntrol['data']['metadata']['code']) || !in_array($addAntrol['data']['metadata']['code'], [200])) {
                    $isFailed = true;

                    // Apabila terdapat duplikat antrean
                    if ($addAntrol['data']['metadata']['code'] == 208) {
                        // Cek status antrean
                        $bookingCodeIsCorrect = $bpjsService->checkQueue(
                            registerNumber: $register['no_rawat'],
                            referralData: $referralData,
                            polyclinicId: $this->polyclinicId,
                            doctorId: $this->doctorId,
                            purposeOfVisit: $this->purposeOfVisit,
                            schedule: $this->schedule,
                            isJkn: true
                        );

                        if ($bookingCodeIsCorrect) {
                            $isFailed = false;
                        } else {
                            // no_rawat sudah dipakai antrean lain — increment sequence dan ulangi
                            $sequence = (int) substr($register['no_rawat'], 9) + 1;
                            $newCareNumber = 'D' . now()->format('Ymd') . \sprintf('%06d', $sequence);

                            Register::where('no_rawat', $register['no_rawat'])->delete();

                            $register = $registerService->insert(
                                $this->patient,
                                $referralData,
                                [
                                    'polyclinicId' => $this->schedule['polyclinic']['kd_poli'],
                                    'polyclinicName' => $this->schedule['polyclinic']['nm_poli'],
                                ],
                                [
                                    'doctorId' => $this->schedule['doctor']['kd_dokter'],
                                    'doctorName' => $this->schedule['doctor']['nm_dokter'],
                                ],
                                $this->payType,
                                false,
                                $newCareNumber
                            );

                            $addAntrol = $bpjsService->addQueue(
                                patient: $this->patient,
                                participantData: $this->participantData,
                                register: $register,
                                doctor: [
                                    'doctorId' => $this->doctorId,
                                    'doctorName' => $this->doctorName,
                                ],
                                polyclinic: [
                                    'polyclinicId' => $this->polyclinicId,
                                    'polyclinicName' => $this->polyclinicName,
                                ],
                                schedule: $this->schedule,
                                referralData: $referralData,
                                purposeOfVisit: $this->purposeOfVisit
                            );

                            if ($addAntrol['data']['metadata']['code'] == 200) {
                                $isFailed = false;
                            }
                        }
                    }

                    if ($isFailed) {
                        DB::rollBack();
                        $errorMessage = $addAntrol['data']['metadata']['message'] ?? 'Gagal menambahkan antrean';
                        LivewireAlert::error()
                            ->title('Gagal Menambahkan Antrean')
                            ->text($errorMessage)
                            ->timer(0)
                            ->show();
                        return;
                    }
                }
            }

            // Catat Log Antrol
            // $antrolLog = DB::table('bridging_antrean_poli_bpjs')
            //     ->updateOrInsert([
            //         'kodebooking' => $register->no_rawat
            //     ], [...$addAntrol['data']['requestdata'], $addAntrol['data']['metadata']['message'], 'APM']);

            $bpjsService = $bpjsService->setService('vclaim');
            $sep = $bpjsService->insertSEP(
                $this->patient,
                $this->participantData,
                [
                    'doctorId' => $this->doctorId,
                    'doctorName' => $this->doctorName,
                ],
                [
                    'polyclinicId' => $this->polyclinicId,
                    'polyclinicName' => $this->polyclinicName,
                ],
                $referralData,
                $this->purposeOfVisit
            );

            // Check SEP insert response
            if (isset($sep['success']) && $sep['success'] === false) {
                DB::connection('simrs')->rollBack();
                $errorMessage = $sep['data']['metaData']['message'] ?? 'Gagal insert SEP';
                LivewireAlert::error()
                    ->title('Gagal Insert SEP')
                    ->text($errorMessage)
                    ->timer(0)
                    ->show();
                return;
            }

            if (!isset($sep['data']['response'])) {
                DB::connection('simrs')->rollBack();
                LivewireAlert::error()
                    ->title('Data SEP Tidak Valid')
                    ->text('Response SEP tidak memiliki data yang valid')
                    ->timer(0)
                    ->show();
                return;
            }

            $sepData = $sep['data']['response'];
            if ($this->purposeOfVisit->name == PurposeOfVisit::KonsulInternal->name) {
                $sep = SepInternal::create([
                    'no_sep' => $sepData['noSep'],
                    'no_rawat' => $register['no_rawat'],
                    'tglsep' => $sepData['tglSep'],
                    'tglrujukan' => $this->refDate,
                    'no_rujukan' => $sepData['noRujukan'],
                    'kdppkrujukan' => $this->refOriginId,
                    'nmppkrujukan' => $this->refOrigin,
                    'kdppkpelayanan' => SettingHelper::get('hospitalBpjsCode'),
                    'nmppkpelayanan' => SettingHelper::get('hospitalName'),
                    'jnspelayanan' => $this->serviceType,
                    'catatan' => $sepData['catatan'],
                    'diagawal' => $this->diagnoseId,
                    'nmdiagnosaawal' => $this->diagnose,
                    'kdpolitujuan' => $this->polyclinicId,
                    'nmpolitujuan' => $this->polyclinicName,
                    'klsrawat' => $this->classRights,
                    'klsnaik' => '',
                    'pembiayaan' => '',
                    'pjnaikkelas' => '',
                    'lakalantas' => '0',
                    'user' => 'APM',
                    'nomr' => $sepData['peserta']['noMr'],
                    'nama_pasien' => $sepData['peserta']['nama'],
                    'tanggal_lahir' => $sepData['peserta']['tglLahir'],
                    'peserta' => $sepData['peserta']['jnsPeserta'],
                    'jkel' => $sepData['peserta']['kelamin'] == 'Perempuan' ? 'P' : 'L',
                    'no_kartu' => $sepData['peserta']['noKartu'],
                    'tglpulang' => null,
                    'asal_rujukan' => $this->refOriginVisit == 2 ? '2. Faskes 2(RS)' : '1. Faskes 1',
                    'eksekutif' => $this->executive ? '1.Ya' : '0. Tidak',
                    'cob' => $this->cob ? '1.Ya' : '0. Tidak',
                    'notelep' => $this->participantData['peserta']['mr']['noTelepon'] ?? $this->patient['no_tlp'],
                    'katarak' => $this->cataract ? '1.Ya' : '0. Tidak',
                    'tglkkl' => null,
                    'keterangankkl' => '',
                    'suplesi' => '0. Tidak',
                    'no_sep_suplesi' => '',
                    'kdprop' => '',
                    'nmprop' => '',
                    'kdkab' => '',
                    'nmkab' => '',
                    'kdkec' => '',
                    'nmkec' => '',
                    'noskdp' => $this->controlNumber,
                    'kddpjp' => $this->doctorId,
                    'nmdpdjp' => $this->doctorName,
                    'tujuankunjungan' => $sepData['tujuanKunj'],
                    'flagprosedur' => $sepData['flagProcedure'],
                    'penunjang' => $sepData['kdPenunjang'],
                    'asesmenpelayanan' => $sepData['assestmenPel'],
                    'kddpjplayanan' => $this->doctorId,
                    'nmdpjplayanan' => $this->doctorName,
                    'backdate' => false,
                    'antrean' => true,
                ]);
            } else {
                $sep = Sep::create([
                    'no_sep' => $sepData['noSep'],
                    'no_rawat' => $register['no_rawat'],
                    'tglsep' => $sepData['tglSep'],
                    'tglrujukan' => $this->refDate,
                    'no_rujukan' => $sepData['noRujukan'],
                    'kdppkrujukan' => $this->refOriginId,
                    'nmppkrujukan' => $this->refOrigin,
                    'kdppkpelayanan' => SettingHelper::get('hospitalBpjsCode'),
                    'nmppkpelayanan' => SettingHelper::get('hospitalName'),
                    'jnspelayanan' => $this->serviceType,
                    'catatan' => $sepData['catatan'],
                    'diagawal' => $this->diagnoseId,
                    'nmdiagnosaawal' => $this->diagnose,
                    'kdpolitujuan' => $this->polyclinicId,
                    'nmpolitujuan' => $this->polyclinicName,
                    'klsrawat' => $this->classRights,
                    'klsnaik' => '',
                    'pembiayaan' => '',
                    'pjnaikkelas' => '',
                    'lakalantas' => '0',
                    'user' => 'APM',
                    'nomr' => $sepData['peserta']['noMr'],
                    'nama_pasien' => $sepData['peserta']['nama'],
                    'tanggal_lahir' => $sepData['peserta']['tglLahir'],
                    'peserta' => $sepData['peserta']['jnsPeserta'],
                    'jkel' => $sepData['peserta']['kelamin'] == 'Perempuan' ? 'P' : 'L',
                    'no_kartu' => $sepData['peserta']['noKartu'],
                    'tglpulang' => null,
                    'asal_rujukan' => $this->refOriginVisit == 2 ? '2. Faskes 2(RS)' : '1. Faskes 1',
                    'eksekutif' => $this->executive ? '1.Ya' : '0. Tidak',
                    'cob' => $this->cob ? '1.Ya' : '0. Tidak',
                    'notelep' => $this->participantData['peserta']['mr']['noTelepon'] ?? $this->patient['no_tlp'],
                    'katarak' => $this->cataract ? '1.Ya' : '0. Tidak',
                    'tglkkl' => null,
                    'keterangankkl' => '',
                    'suplesi' => '0. Tidak',
                    'no_sep_suplesi' => '',
                    'kdprop' => '',
                    'nmprop' => '',
                    'kdkab' => '',
                    'nmkab' => '',
                    'kdkec' => '',
                    'nmkec' => '',
                    'noskdp' => $this->controlNumber,
                    'kddpjp' => $this->doctorId,
                    'nmdpdjp' => $this->doctorName,
                    'tujuankunjungan' => $sepData['tujuanKunj'],
                    'flagprosedur' => $sepData['flagProcedure'],
                    'penunjang' => $sepData['kdPenunjang'],
                    'asesmenpelayanan' => $sepData['assestmenPel'],
                    'kddpjplayanan' => $this->doctorId,
                    'nmdpjplayanan' => $this->doctorName,
                    'backdate' => false,
                    'antrean' => true,
                ]);
            }

            $this->addDiagnose($register['no_rawat']);

            DB::connection('simrs')->commit();

            $this->dispatch('elegtabilityData', [
                'status' => true,
                'registration' => $register,
                'sep' => $sep
            ]);
        } catch (\Exception $e) {
            DB::connection('simrs')->rollBack();
            LivewireAlert::error()
                ->title('Error')
                ->text($e->getMessage())
                ->timer(0)
                ->show();
            return;
        }
    }
    public function exeJkn()
    {
        try {
            DB::beginTransaction();
            $referralData = [
                'refOrigin' => $this->refOrigin,
                'refOriginId' => $this->refOriginId,
                'refOriginVisit' => $this->refOriginVisit,
                'refNumber' => $this->refNumber,
                'refDate' => $this->refDate,
                'controlNumber' => $this->controlNumber,
                'diagnose' => $this->diagnose,
                'diagnoseId' => $this->diagnoseId,
                'serviceType' => $this->serviceType,
                'classRights' => $this->classRights,
                'note' => $this->note,
                'serviceAssessment' => $this->serviceAssessment,
                'procedureFlag' => $this->procedureFlag,
                'support' => $this->support,
                'executive' => $this->executive,
                'cob' => $this->cob,
                'cataract' => $this->cataract,
            ];

            $bpjsService = new BpjsService($this->participantNumber);
            $sep = $bpjsService->insertSEP(
                $this->patient,
                $this->participantData,
                [
                    'doctorId' => $this->doctorId,
                    'doctorName' => $this->doctorName,
                ],
                [
                    'polyclinicId' => $this->polyclinicId,
                    'polyclinicName' => $this->polyclinicName,
                ],
                $referralData,
                $this->purposeOfVisit
            );

            // Check SEP insert response
            if (isset($sep['success']) && $sep['success'] === false) {
                DB::rollBack();
                $errorMessage = $sep['data']['metaData']['message'] ?? 'Gagal insert SEP';
                LivewireAlert::error()
                    ->title('Gagal Insert SEP')
                    ->text($errorMessage)
                    ->timer(0)
                    ->show();
                return;
            }

            if (!isset($sep['data']['response'])) {
                DB::rollBack();
                LivewireAlert::error()
                    ->title('Data SEP Tidak Valid')
                    ->text('Response SEP tidak memiliki data yang valid')
                    ->timer(0)
                    ->show();
                return;
            }

            $sepData = $sep['data']['response'];
            $noRawat = JknRef::where('nomorkartu', $this->participantNumber)
                ->where('tanggalperiksa', now()->setTimezone('Asia/Jakarta')->format('Y-m-d'))
                ->where('status', '<>', 'Batal')
                ->first()->no_rawat;

            if ($this->purposeOfVisit->name == PurposeOfVisit::KonsulInternal->name) {
                $sep = SepInternal::create([
                    'no_sep' => $sepData['noSep'],
                    'no_rawat' => $noRawat,
                    'tglsep' => $sepData['tglSep'],
                    'tglrujukan' => $this->refDate,
                    'no_rujukan' => $sepData['noRujukan'],
                    'kdppkrujukan' => $this->refOriginId,
                    'nmppkrujukan' => $this->refOrigin,
                    'kdppkpelayanan' => SettingHelper::get('hospitalBpjsCode'),
                    'nmppkpelayanan' => SettingHelper::get('hospitalName'),
                    'jnspelayanan' => $this->serviceType,
                    'catatan' => $sepData['catatan'],
                    'diagawal' => $this->diagnoseId,
                    'nmdiagnosaawal' => $this->diagnose,
                    'kdpolitujuan' => $this->polyclinicId,
                    'nmpolitujuan' => $this->polyclinicName,
                    'klsrawat' => $this->classRights,
                    'klsnaik' => '',
                    'pembiayaan' => '',
                    'pjnaikkelas' => '',
                    'lakalantas' => '0',
                    'user' => 'APM',
                    'nomr' => $sepData['peserta']['noMr'],
                    'nama_pasien' => $sepData['peserta']['nama'],
                    'tanggal_lahir' => $sepData['peserta']['tglLahir'],
                    'peserta' => $sepData['peserta']['jnsPeserta'],
                    'jkel' => $sepData['peserta']['kelamin'] == 'Perempuan' ? 'P' : 'L',
                    'no_kartu' => $sepData['peserta']['noKartu'],
                    'tglpulang' => null,
                    'asal_rujukan' => $this->refOriginVisit == 2 ? '2. Faskes 2(RS)' : '1. Faskes 1',
                    'eksekutif' => $this->executive ? '1.Ya' : '0. Tidak',
                    'cob' => $this->cob ? '1.Ya' : '0. Tidak',
                    'notelep' => $this->participantData['peserta']['mr']['noTelepon'] ?? $this->patient['no_tlp'],
                    'katarak' => $this->cataract ? '1.Ya' : '0. Tidak',
                    'tglkkl' => null,
                    'keterangankkl' => '',
                    'suplesi' => '0. Tidak',
                    'no_sep_suplesi' => '',
                    'kdprop' => '',
                    'nmprop' => '',
                    'kdkab' => '',
                    'nmkab' => '',
                    'kdkec' => '',
                    'nmkec' => '',
                    'noskdp' => $this->controlNumber,
                    'kddpjp' => $this->doctorId,
                    'nmdpdjp' => $this->doctorName,
                    'tujuankunjungan' => $sepData['tujuanKunj'],
                    'flagprosedur' => $sepData['flagProcedure'],
                    'penunjang' => $sepData['kdPenunjang'],
                    'asesmenpelayanan' => $sepData['assestmenPel'],
                    'kddpjplayanan' => $this->doctorId,
                    'nmdpjplayanan' => $this->doctorName,
                    'backdate' => false,
                    'antrean' => true,
                ]);
            } else {
                $sep = Sep::create([
                    'no_sep' => $sepData['noSep'],
                    'no_rawat' => $noRawat,
                    'tglsep' => $sepData['tglSep'],
                    'tglrujukan' => $this->refDate,
                    'no_rujukan' => $sepData['noRujukan'],
                    'kdppkrujukan' => $this->refOriginId,
                    'nmppkrujukan' => $this->refOrigin,
                    'kdppkpelayanan' => SettingHelper::get('hospitalBpjsCode'),
                    'nmppkpelayanan' => SettingHelper::get('hospitalName'),
                    'jnspelayanan' => $this->serviceType,
                    'catatan' => $sepData['catatan'],
                    'diagawal' => $this->diagnoseId,
                    'nmdiagnosaawal' => $this->diagnose,
                    'kdpolitujuan' => $this->polyclinicId,
                    'nmpolitujuan' => $this->polyclinicName,
                    'klsrawat' => $this->classRights,
                    'klsnaik' => '',
                    'pembiayaan' => '',
                    'pjnaikkelas' => '',
                    'lakalantas' => '0',
                    'user' => 'APM',
                    'nomr' => $sepData['peserta']['noMr'],
                    'nama_pasien' => $sepData['peserta']['nama'],
                    'tanggal_lahir' => $sepData['peserta']['tglLahir'],
                    'peserta' => $sepData['peserta']['jnsPeserta'],
                    'jkel' => $sepData['peserta']['kelamin'] == 'Perempuan' ? 'P' : 'L',
                    'no_kartu' => $sepData['peserta']['noKartu'],
                    'tglpulang' => null,
                    'asal_rujukan' => $this->refOriginVisit == 2 ? '2. Faskes 2(RS)' : '1. Faskes 1',
                    'eksekutif' => $this->executive ? '1.Ya' : '0. Tidak',
                    'cob' => $this->cob ? '1.Ya' : '0. Tidak',
                    'notelep' => $this->participantData['peserta']['mr']['noTelepon'] ?? $this->patient['no_tlp'],
                    'katarak' => $this->cataract ? '1.Ya' : '0. Tidak',
                    'tglkkl' => null,
                    'keterangankkl' => '',
                    'suplesi' => '0. Tidak',
                    'no_sep_suplesi' => '',
                    'kdprop' => '',
                    'nmprop' => '',
                    'kdkab' => '',
                    'nmkab' => '',
                    'kdkec' => '',
                    'nmkec' => '',
                    'noskdp' => $this->controlNumber,
                    'kddpjp' => $this->doctorId,
                    'nmdpdjp' => $this->doctorName,
                    'tujuankunjungan' => $sepData['tujuanKunj'],
                    'flagprosedur' => $sepData['flagProcedure'],
                    'penunjang' => $sepData['kdPenunjang'],
                    'asesmenpelayanan' => $sepData['assestmenPel'],
                    'kddpjplayanan' => $this->doctorId,
                    'nmdpjplayanan' => $this->doctorName,
                    'backdate' => false,
                    'antrean' => true,
                ]);
            }

            $this->addDiagnose($noRawat);

            DB::commit();

            $this->dispatch('elegtabilityData', [
                'status' => true,
                'sep' => $sep
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            LivewireAlert::error()
                ->title('Error')
                ->text($e->getMessage())
                ->timer(0)
                ->show();
            return;
        }
    }

    private function addDiagnose(string $noRawat)
    {
        $isOldDiagnose = PatientDiagnose::query()
            ->join('reg_periksa', 'diagnosa_pasien.no_rawat', '=', 'reg_periksa.no_rawat')
            ->where('reg_periksa.no_rkm_medis', $this->mrNumber)
            ->where('diagnosa_pasien.kd_penyakit', $this->diagnoseId)
            ->exists();

        PatientDiagnose::updateOrCreate(
            [
                'no_rawat' => $noRawat,
                'kd_penyakit' => $this->diagnoseId,
                'status' => 'Ralan',
            ],
            [
                'prioritas' => 1,
                'status_penyakit' => $isOldDiagnose ? 'Lama' : 'Baru',
            ]
        );
    }
}
