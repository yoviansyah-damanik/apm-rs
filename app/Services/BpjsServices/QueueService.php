<?php

namespace App\Services\BpjsServices;

use App\Models\Patient;
use App\Models\Register;
use App\Services\ApiService;
use App\Enums\PurposeOfVisit;
use App\Services\DecryptionService;

trait QueueService
{
    public function addQueue(
        array|Patient $patient,
        array $participantData,
        array|Register $register,
        array $doctor,
        array $polyclinic,
        array $schedule,
        array $referralData,
        string|PurposeOfVisit $purposeOfVisit,
        bool $isJkn = true,
    ): array {
        $patientRegisteredCount = Register::where('kd_poli', $polyclinic['polyclinicId'])
            ->where('tgl_registrasi', now()->format('Y-m-d'))
            ->where('stts', '<>', 'Batal')
            ->count();

        $data = [
            "kodebooking" => $register['no_rawat'],
            "jenispasien" => $isJkn ? "JKN" : "NON JKN",
            "nomorkartu" => $participantData['peserta']['noKartu'],
            "nik" => $participantData['peserta']['nik'],
            "nohp" => $participantData['peserta']['mr']['noTelepon'] ?? $patient['no_tlp'],
            "kodepoli" => $polyclinic['polyclinicId'],
            "namapoli" => $polyclinic['polyclinicName'],
            "pasienbaru" => 0,
            "norm" => $patient['no_rkm_medis'],
            "tanggalperiksa" => now()->setTimezone('Asia/Jakarta')->format('Y-m-d'),
            "kodedokter" => $doctor['doctorId'],
            "namadokter" => $doctor['doctorName'],
            "jampraktek" => \Carbon\Carbon::parse($schedule['jam_mulai'])->format('H:i') . '-' . \Carbon\Carbon::parse($schedule['jam_selesai'])->format('H:i'),
            "jeniskunjungan" => $isJkn ? (in_array($purposeOfVisit->name, ['KontrolPostRanap', 'Kontrol']) ? '3' : ($referralData['refOriginVisit'] == 2 ? '4' : ($purposeOfVisit->name == PurposeOfVisit::RujukInternal->name ? '2' : '1'))) : '3',
            "nomorreferensi" => $isJkn ? (in_array($purposeOfVisit->name, ['KontrolPostRanap', 'Kontrol']) ? $referralData['controlNumber'] : $referralData['refNumber']) : "-",
            "nomorantrean" => $polyclinic['polyclinicId'] . '-' . $register['no_reg'],
            "angkaantrean" => (int) $register['no_reg'],
            "estimasidilayani" => \Carbon\Carbon::parse($schedule['jam_mulai'])->addMinutes(($register['no_reg'] - 1) * 6)->timestamp * 1000, // Convert to milliseconds
            "sisakuotajkn" => $schedule['kuota'] - $patientRegisteredCount,
            "kuotajkn" => $schedule['kuota'],
            "sisakuotanonjkn" => $schedule['kuota'] - $patientRegisteredCount,
            "kuotanonjkn" => $schedule['kuota'],
            "keterangan" => "Peserta harap tiba 30 menit lebih awal guna pencatatan administrasi."
        ];

        $payload = ApiService::hitApiWithParams(
            $this->baseUrl . '/antrean/add',
            $data,
            'POST',
            $this->getHeaders()
        );

        $payload = [
            ...$payload,
            'data' => [
                'metadata' => $payload['data']['metadata'],
                'requestdata' => $data
            ]
        ];

        return $payload;
    }

    public function checkQueue(
        string $registerNumber,
        array $referralData,
        string $polyclinicId,
        string $doctorId,
        string|PurposeOfVisit $purposeOfVisit,
        array $schedule,
        bool $isJkn = true
    ): bool {
        $payload = ApiService::hitApiWithoutParams(
            $this->baseUrl . '/antrean/pendaftaran/kodebooking/' . $registerNumber,
            'GET',
            $this->getHeaders()
        );

        $decrypted = DecryptionService::decryptAndDecompress(
            $this->consId . $this->secretKey . $this->signature['timestamp'],
            $payload['data']['response']
        );
        $payload['data'] = json_decode($decrypted['data'], true)[0];


        $jenisKunjungan = $isJkn ? (in_array($purposeOfVisit->name, ['KontrolPostRanap', 'Kontrol']) ? '3' : ($referralData['refOriginVisit'] == 2 ? '4' : ($purposeOfVisit->name == PurposeOfVisit::RujukInternal->name ? '2' : '1'))) : '3';
        $nomorReferensi = $isJkn ? (in_array($purposeOfVisit->name, ['KontrolPostRanap', 'Kontrol']) ? $referralData['controlNumber'] : $referralData['refNumber']) : "-";
        $jamPraktek = \Carbon\Carbon::parse($schedule['jam_mulai'])->format('H:i') . '-' . \Carbon\Carbon::parse($schedule['jam_selesai'])->format('H:i');

        return $jenisKunjungan == $payload['data']['jeniskunjungan']
            && $nomorReferensi == $payload['data']['nomorreferensi']
            && $polyclinicId == $payload['data']['kodepoli']
            && $doctorId == $payload['data']['kodedokter']
            && $jamPraktek == $payload['data']['jampraktek'];
    }
}
