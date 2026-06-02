<?php

namespace App\Livewire\CheckIn;

use App\Helpers\MagicHelper;
use App\Models\Register;
use App\Models\Schedule;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class JknOrder extends Component
{
    public string $careCode = 'B';
    public array $polyclinics = [];

    public function mount(string $careCode = 'B'): void
    {
        $this->careCode = $careCode;
        $this->loadPolyclinics();
    }

    public function render()
    {
        return view('livewire.check-in.jkn-order');
    }

    private function loadPolyclinics(): void
    {
        $excludePoliIds = MagicHelper::parseEnvArray(env('EXCLUDE_JKN_POLYCLINICS', '[]'));
        $excludeDokterIds = MagicHelper::parseEnvArray(env('EXCLUDE_JKN_DOCTORS', '[]'));

        $allSchedules = Schedule::scheduledToday()
            ->when($excludePoliIds, fn($q) => $q->whereNotIn('kd_poli', $excludePoliIds))
            ->when($excludeDokterIds, fn($q) => $q->whereNotIn('kd_dokter', $excludeDokterIds))
            ->get();

        $activeKeys = Schedule::scheduledToday()
            ->limitedTime()
            ->when($excludePoliIds, fn($q) => $q->whereNotIn('kd_poli', $excludePoliIds))
            ->when($excludeDokterIds, fn($q) => $q->whereNotIn('kd_dokter', $excludeDokterIds))
            ->get()
            ->map(fn($s) => "{$s->kd_poli}|{$s->kd_dokter}")
            ->unique()
            ->flip();

        // Flat list: satu entry per kombinasi poli+dokter
        $this->polyclinics = $allSchedules
            ->map(function ($s) use ($activeKeys) {
                $key = "{$s->kd_poli}|{$s->kd_dokter}";
                return [
                    'key' => $key,
                    'kd_poli' => $s->kd_poli,
                    'nm_poli' => optional($s->polyclinic)->nm_poli ?? '-',
                    'kd_dokter' => $s->kd_dokter,
                    'nm_dokter' => optional($s->doctor)->nm_dokter ?? '-',
                    'jam_check_in' => \Carbon\Carbon::parse(substr($s->jam_mulai, 0, 5))->subMinutes(30)->format("H:i"),
                    'jam_mulai' => substr($s->jam_mulai, 0, 5),
                    'jam_selesai' => substr($s->jam_selesai, 0, 5),
                    'is_active' => isset($activeKeys[$key]),
                ];
            })
            ->unique('key')
            ->sort(function ($a, $b) {
                if ($a['is_active'] !== $b['is_active']) {
                    return $b['is_active'] <=> $a['is_active'];
                }
                $poliCmp = strcmp($a['nm_poli'], $b['nm_poli']);
                return $poliCmp !== 0 ? $poliCmp : strcmp($a['nm_dokter'], $b['nm_dokter']);
            })
            ->values()
            ->toArray();
    }

    /** Dipanggil dari Alpine via $wire.getQueues() */
    public function getQueues(string $kdPoli, string $kdDokter): array
    {
        return Register::query()
            ->whereHas(
                'jknRef',
                fn($q) => $q->where('status', 'Belum')
            )
            ->whereDate('tgl_registrasi', today())
            ->where('kd_poli', $kdPoli)
            ->where('kd_dokter', $kdDokter)
            ->with('patient')
            ->orderBy('no_reg')
            ->get()
            ->map(fn($r) => [
                'no_rawat' => $r->no_rawat,
                'no_reg' => $r->no_reg,
                'kd_poli' => $r->kd_poli,
                'nm_pasien' => $r->patient?->nm_pasien ?? '-',
            ])
            ->toArray();
    }

    public function selectQueue(string $noRawat): void
    {
        $this->dispatch('queueSelected', noRawat: $noRawat);
        Flux::modal('checkViaPoli')->close();
    }



    #[On('resetSchedules')]
    public function resetSchedules(): void
    {
        $this->dispatch('schedules-reset', polyclinics: $this->polyclinics);
        $this->loadPolyclinics();
        Flux::modal('checkViaPoli')->show();
    }
}
