<?php

namespace App\Livewire\OldPatient;

use App\Models\Register;
use Livewire\Component;
use Livewire\Attributes\Reactive;

class Finish extends Component
{
    #[Reactive]
    public $registration;
    public $registrationData = [];

    public function mount()
    {
        // Muat data registrasi beserta relasinya
        if ($this->registration) {
            $register = Register::with(['patient', 'doctor', 'polyclinic', 'payType'])
                ->where('no_rawat', $this->registration['no_rawat'])
                ->first();

            if ($register) {
                // Ambil schedule berdasarkan kd_poli dan kd_dokter (composite key)
                $schedule = $register->getSchedule();

                // Hitung estimasi waktu dilayani
                // Rumus: jam_mulai + ((no_reg - 1) * 6 menit)
                $estimasiDilayani = '-';
                if ($schedule && $schedule->jam_mulai) {
                    try {
                        $jamMulai = \Carbon\Carbon::parse($schedule->jam_mulai);
                        $estimasiTime = $jamMulai->addMinutes(((int) $register->no_reg - 1) * 6);
                        $estimasiDilayani = $estimasiTime->format('H:i');
                    } catch (\Exception $e) {
                        $estimasiDilayani = $register->jam_reg ?? '-';
                    }
                } else {
                    $estimasiDilayani = $register->jam_reg ?? '-';
                }

                $this->registrationData = [
                    'no_reg' => $register->no_reg,
                    'no_rawat' => $register->no_rawat,
                    'tgl_registrasi' => $register->tgl_registrasi,
                    'jam_reg' => $register->jam_reg,
                    'kd_poli' => $register->kd_poli,
                    'no_rkm_medis' => $register->no_rkm_medis,
                    'nm_pasien' => $register->patient->nm_pasien ?? '-',
                    'jk' => $register->patient->jk ?? '-',
                    'nm_poli' => $register->polyclinic->nm_poli ?? '-',
                    'nm_dokter' => $register->doctor->nm_dokter ?? '-',
                    'png_jawab' => $register->payType->png_jawab ?? '-',
                    'estimasi_dilayani' => $estimasiDilayani,
                ];
            }
        }
    }

    public function backToHome()
    {
        return $this->redirectRoute('home', navigate: true);
    }

    public function render()
    {
        return view('livewire.old-patient.finish');
    }

    public function placeholder()
    {
        return view('placeholders.finish');
    }
}
