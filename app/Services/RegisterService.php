<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Register;
use App\Models\BpjsDoctor;
use App\Models\Polyclinic;
use App\Models\ReferralIn;
use App\Models\BpjsPolyclinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Services\ActivityLogService;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class RegisterService
{
    public function __construct()
    {
        //
    }

    /**
     * Summary of getRegisterNumber
     * @param string $polyclinic    Kode Poli
     * @return void
     */
    public function getRegisterNumber(string $polyclinic, string $doctor): string
    {
        $regNumber = Register::selectRaw("ifnull(MAX(CONVERT(no_reg,signed)),0) as no_reg")
            ->whereDate('tgl_registrasi', now())
            ->where('kd_poli', $polyclinic)
            ->where('kd_dokter', $doctor)
            ->where('stts', '!=', 'Batal')
            ->first()->no_reg + 1;

        return sprintf('%03d', $regNumber);
    }

    // Nomor Rawat Custom RST Tk. IV 01.07.03 Padangsidimpuan
    public function getCareNumber(?\Carbon\Carbon $date = null): string
    {
        try {
            $date ??= now()->format('Y-m-d');

            $regNumber = Register::selectRaw("ifnull(MAX(CONVERT(RIGHT(no_rawat,6),signed)),0) as no_reg")
                ->whereDate('tgl_registrasi', $date)
                ->where('no_rawat', 'like', 'D%')
                ->first()->no_reg + 1;

            return 'D' . now()->format('Ymd') . sprintf('%06d', $regNumber);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    // No Rawat Bawaan Khanza
    // public static function getRegisterNumber(): string
    // {
    //     try {
    //         $regNumber = Register::selectRaw("ifnull(MAX(CONVERT(RIGHT(no_rawat,6),signed)),0) as no_rawat")
    //             ->whereDate('tgl_registrasi', now())
    //             ->where('no_rawat', 'like', '%A%')
    //             ->first()->no_rawat + 1;

    //         return now()->format('Y/m/d') . '/' . sprintf('%06d', $regNumber);
    //     } catch (\Exception $e) {
    //         dd($e->getMessage());
    //     }
    // }

    public function insert(
        array|Patient $patient,
        array|null $referralData,
        array $polyclinic,
        array $doctor,
        array|string|null $payType = null,
        bool $isJkn = true,
        ?string $customCareNumber = null,
    ) {
        try {
            DB::connection('simrs')->beginTransaction();

            if ($isJkn) {
                $polyclinicIdRs = BpjsPolyclinic::where('kd_poli_bpjs', $polyclinic['polyclinicId'])->first()->kd_poli_rs;
                $doctorIdRs = BpjsDoctor::where('kd_dokter_bpjs', $doctor['doctorId'])->first()->kd_dokter;
            } else {
                $polyclinicIdRs = $polyclinic['polyclinicId'];
                $doctorIdRs = $doctor['doctorId'];
            }

            $regNumber = $this->getRegisterNumber($polyclinicIdRs, $doctorIdRs);
            $careNumber = $customCareNumber ?? $this->getCareNumber();

            $patientsAge = Patient::selectRaw('TIMESTAMPDIFF(YEAR, pasien.tgl_lahir, CURDATE()) as tahun,' .
                '(TIMESTAMPDIFF(MONTH, pasien.tgl_lahir, CURDATE()) - ((TIMESTAMPDIFF(MONTH, pasien.tgl_lahir, CURDATE()) div 12) * 12)) as bulan,'
                . 'TIMESTAMPDIFF(DAY, DATE_ADD(DATE_ADD(pasien.tgl_lahir,INTERVAL TIMESTAMPDIFF(YEAR, pasien.tgl_lahir, CURDATE()) YEAR), INTERVAL TIMESTAMPDIFF(MONTH, pasien.tgl_lahir, CURDATE()) - ((TIMESTAMPDIFF(MONTH, pasien.tgl_lahir, CURDATE()) div 12) * 12) MONTH), CURDATE()) as hari')
                ->where('no_rkm_medis', $patient['no_rkm_medis'])
                ->firstOrFail();

            $register = Register::create([
                'no_reg' => $regNumber,
                'no_rawat' => $careNumber,
                'tgl_registrasi' => now()->format("Y-m-d"),
                'jam_reg' => now()->format("H:i:s"),
                'kd_dokter' => $doctorIdRs,
                'no_rkm_medis' => $patient['no_rkm_medis'],
                'kd_poli' => $polyclinicIdRs,
                'p_jawab' => $patient['namakeluarga'],
                'almt_pj' => $patient['alamatpj'] . ', ' . $patient['kelurahanpj'] . ', ' . $patient['kecamatanpj'] . ', ' . $patient['kabupatenpj'],
                'hubunganpj' => $patient['keluarga'],
                'biaya_reg' => Polyclinic::where('kd_poli', $polyclinic['polyclinicId'])->first()->registrasi,
                'stts' => 'Belum',
                'stts_daftar' => Register::selectRaw('count(reg_periksa.no_rkm_medis) as jumlah_reg')
                    ->where('no_rkm_medis', $patient['no_rkm_medis'])
                    ->first()->jumlah_reg > 0 ? 'Lama' : 'Baru',
                'status_lanjut' => 'Ralan',
                'kd_pj' => $isJkn ? 'BPJ' : (is_array($payType) ? $payType['kd_pj'] : $payType),
                'umurdaftar' => $patientsAge->tahun > 0 ? $patientsAge->tahun : ($patientsAge->bulan > 0 ? $patientsAge->bulan : $patientsAge->hari),
                'sttsumur' => $patientsAge->tahun > 0 ? 'Th' : ($patientsAge->bulan > 0 ? 'Bl' : 'Hr'),
                'status_bayar' => 'Belum Bayar',
                'status_poli' => Register::selectRaw('count(reg_periksa.no_rkm_medis) as jumlah_reg')
                    ->where('no_rkm_medis', $patient['no_rkm_medis'])
                    ->where('kd_poli', $polyclinic['polyclinicId'])
                    ->first()->jumlah_reg > 0 ? 'Lama' : 'Baru',
            ]);

            if ($referralData) {
                $this->insertReferralIn($careNumber, [
                    'perujuk' => $referralData['refOriginId'] . ' - ' . $referralData['refOrigin'],
                    'alamat' => '-',
                    'no_rujuk' => $referralData['refNumber'],
                    'dokter_perujuk' => '-',
                    'kd_penyakit' => $referralData['diagnoseId'],
                ], $polyclinicIdRs);
            }

            DB::connection('simrs')->commit();

            ActivityLogService::success('database', 'ambil_antrean_poli', "Pendaftaran berhasil: {$careNumber}", [
                'no_rawat'     => $careNumber,
                'no_rkm_medis' => is_array($patient) ? $patient['no_rkm_medis'] : $patient->no_rkm_medis,
                'kd_poli'      => $polyclinicIdRs,
                'kd_dokter'    => $doctorIdRs,
                'no_reg'       => $regNumber,
            ]);

            return $register;
        } catch (\Exception $e) {
            DB::connection('simrs')->rollBack();

            ActivityLogService::error('database', 'ambil_antrean_poli', 'Gagal menyimpan pendaftaran: ' . $e->getMessage(), [
                'no_rkm_medis' => is_array($patient) ? ($patient['no_rkm_medis'] ?? null) : ($patient->no_rkm_medis ?? null),
                'error'        => $e->getMessage(),
            ]);

            LivewireAlert::error()
                ->title('Notifikasi Pendaftaran')
                ->text($e->getMessage())
                ->timer(0)
                ->show();
        }
    }

    public function insertReferralIn(string $careNumber, array $referral, string $polyclinic)
    {
        $referralNumber = ReferralIn::where(
            'no_balasan',
            'like',
            'BR/' . now()->format('Y/m/d') . '/%'
        )
            ->orderBy('no_balasan', 'desc')
            ->first();
        $referralNumber = $referralNumber ? substr($referralNumber->no_balasan, -4) : '0001';

        $polyclinicCategory = '-';
        switch ($polyclinic) {
            case 'ANA':
                $polyclinicCategory = 'Anak';
                break;
            case 'BED':
                $polyclinicCategory = 'Bedah';
                break;
            case 'OBG':
                $polyclinicCategory = 'Kebidanan';
                break;
        }

        ReferralIn::create([
            'no_rawat' => $careNumber,
            'perujuk' => $referral['perujuk'],
            'alamat' => $referral['alamat'],
            'no_rujuk' => $referral['no_rujuk'],
            'jm_perujuk' => '0',
            'dokter_perujuk' => $referral['dokter_perujuk'],
            'kd_penyakit' => $referral['kd_penyakit'],
            'kategori_rujuk' => $polyclinicCategory,
            'keterangan' => 'APM',
            'no_balasan' => 'BR/' . now()->format('Y/m/d') . '/' . $referralNumber
        ]);
    }
}
