<?php

namespace App\Repository;

use App\Helpers\MagicHelper;
use App\Models\Register;
use App\Models\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScheduleRepository
{
    private $schedules;

    public function __construct()
    {
        $this->schedules = Schedule::join("maping_dokter_dpjpvclaim", 'maping_dokter_dpjpvclaim.kd_dokter', '=', 'jadwal.kd_dokter', 'inner');
    }

    public function getAll(bool $withQuota = false)
    {
        $this->schedules = collect($this->schedules
            ->get()->toArray());

        if ($withQuota)
            return $this->withQuota()
                ->get();

        return $this->get();
    }

    /**
     * Get today's schedules with caching
     * Cache for 5 minutes to reduce database load
     */
    public function getSchedulesToday(bool $withLimitedTime = false): static
    {
        // $cacheKey = 'schedules_today_' . now()->format('Y-m-d_H');

        $this->schedules = $this->schedules
            ->scheduledToday()
            ->when($withLimitedTime, function ($query) {
                return $query->limitedTime();
            })
            ->get()
            ->sortByDesc('berlangsung');

        return $this;
    }

    public function checkReference(array|null $reference): static
    {
        if (isset($reference) && $reference != null) {
            // Filter schedules berdasarkan kode poli rujukan
            $kdPoliRujukan = $reference['poliRujukan']['kode'] ?? '';

            $this->schedules = $this->schedules
                ->filter(function ($schedule) use ($kdPoliRujukan) {
                    $kdPoli = $schedule['kd_poli'] ?? '';
                    // Cek apakah kode poli dimulai dengan kode poli rujukan
                    return str_starts_with($kdPoli, $kdPoliRujukan);
                });
        }

        return $this;
    }

    public function checkControlLetter(array|null $controlLetter): static
    {
        if (isset($controlLetter) && $controlLetter != null) {
            // Filter schedules berdasarkan kode poli dari surat kontrol
            $kdPoliKontrol = $controlLetter['poliTujuan'] ?? '';

            $this->schedules = $this->schedules
                ->filter(function ($schedule) use ($kdPoliKontrol) {
                    $kdPoliSchedule = $schedule['kd_poli'] ?? '';
                    // Cek apakah kode poli dimulai dengan kode poli kontrol
                    $poliMatch = str_starts_with($kdPoliSchedule, $kdPoliKontrol);

                    return $poliMatch;
                });
        }

        return $this;
    }

    /** Kembalikan nama-nama hari yang memiliki jadwal aktif (misal: ['SENIN', 'RABU', 'JUMAT']) */
    public function getActiveDayNames(): array
    {
        $result = Schedule::join("maping_dokter_dpjpvclaim", 'maping_dokter_dpjpvclaim.kd_dokter', '=', 'jadwal.kd_dokter', 'inner')
            ->whereHas('polyclinic', fn($q) => $q->active())
            ->whereHas('doctor', fn($q) => $q->active())
            ->distinct()
            ->pluck('jadwal.hari_kerja')
            ->toArray();

        return !empty($result) ? $result : ['AKHAD', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];
    }

    /** Ambil jadwal berdasarkan nama hari (SENIN, SELASA, dll) */
    public function getByDay(string $day): static
    {
        $this->schedules = $this->schedules
            ->with([
                'polyclinic' => fn($q) => $q->active(),
                'doctor' => fn($q) => $q->active(),
            ])
            ->whereHas('polyclinic', fn($q) => $q->active())
            ->whereHas('doctor', fn($q) => $q->active())
            ->where('hari_kerja', $day);

        return $this;
    }

    /** Ambil jadwal untuk satu tanggal tertentu */
    public function getByDate(\Carbon\Carbon|Date $startDate, \Carbon\Carbon|Date $endDate)
    {
        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format('Y-m-d');

        $dayNames = collect(\Carbon\CarbonPeriod::create($startDate, $endDate))
            ->map(fn($d) => MagicHelper::getDaysName($d->dayOfWeek))
            ->unique()
            ->values()
            ->toArray();

        $schedules = $this->schedules
            ->with([
                'polyclinic' => fn($q) => $q->active(),
                'doctor' => fn($q) => $q->active(),
            ])
            ->whereHas('polyclinic', fn($q) => $q->active())
            ->whereHas('doctor', fn($q) => $q->active())
            ->whereIn('hari_kerja', $dayNames)
            ->get();

        $quotaUsages = Register::select('kd_poli', 'tgl_registrasi', 'kd_dokter', DB::raw('count(*) as used'))
            ->whereBetween('tgl_registrasi', [$startDate, $endDate])
            ->where('stts', '<>', 'Batal')
            ->groupBy('tgl_registrasi', 'kd_poli', 'kd_dokter')
            ->get();

        $quotaIndex = $quotaUsages->keyBy(fn($u) => $u->tgl_registrasi . '|' . $u->kd_poli . '|' . $u->kd_dokter);
        $schedulesByDay = $schedules->groupBy('hari_kerja');

        $results = [];
        foreach (\Carbon\CarbonPeriod::create($startDate, $endDate) as $date) {
            $dateStr = $date->format('Y-m-d');
            $day = MagicHelper::getDaysName($date->dayOfWeek);
            $dayScheds = $schedulesByDay->get($day, collect());

            $results[$dateStr] = $dayScheds->map(function ($schedule) use ($dateStr, $quotaIndex) {
                $key = "{$dateStr}|{$schedule['kd_poli']}|{$schedule['kd_dokter']}";
                $used = $quotaIndex->get($key)?->used ?? 0;
                $quota = $schedule['kuota'] ?? 0;
                return [
                    ...$schedule->toArray(),
                    'kuota_terpakai' => $used,
                    'sisa_kuota' => $quota - $used,
                ];
            })->values()->toArray();
        }

        return $results;
    }

    public function excludePolyclinics(array|string $polyclinics): static
    {
        // Normalize ke array untuk mempermudah pemrosesan
        $polyclinicsArray = is_string($polyclinics) ? [$polyclinics] : $polyclinics;

        // Filter schedule, exclude yang kd_poli-nya dimulai dengan salah satu dari polyclinics
        $this->schedules = $this->schedules
            ->filter(function ($schedule) use ($polyclinicsArray) {
                $kdPoliSchedule = $schedule['kd_poli'] ?? '';

                // Cek apakah kd_poli dimulai dengan salah satu dari polyclinics yang di-exclude
                foreach ($polyclinicsArray as $excludedPoli) {
                    if (str_starts_with($kdPoliSchedule, $excludedPoli)) {
                        return false; // Exclude schedule ini
                    }
                }

                return true; // Keep schedule ini
            });

        return $this;
    }

    /**
     * Add quota information to schedules
     * Optimized: Single query for all quotas instead of N+1
     *
     * @param bool $isLimitQuota Filter out schedules with no remaining quota
     * @return static
     */
    public function withQuota(bool $isLimitQuota = false, ?string $date = null): static
    {
        // Fetch all quota usage in single query (prevents N+1)
        $quotaUsage = Register::select('kd_poli', 'kd_dokter', DB::raw("concat(kd_dokter,'|',kd_poli) as id"), DB::raw('count(*) as used'))
            ->when(
                $date,
                fn($query) => $query->whereDate('tgl_registrasi', \Carbon\Carbon::parse($date)->format('Y-m-d')),
                fn($query) => $query->active()
            )
            ->groupBy(['kd_poli', 'kd_dokter'])
            ->pluck('used', "id");

        // Map schedules with quota information
        $this->schedules = $this->schedules
            ->map(function ($schedule) use ($quotaUsage) {
                $used = $quotaUsage[$schedule['kd_dokter'] . '|' . $schedule['kd_poli']] ?? 0;
                $quota = $schedule['kuota'] ?? 0;
                $sisaKuota = $quota - $used;

                return [
                    ...$schedule->toArray(),
                    'sisa_kuota' => $sisaKuota,
                    'kuota_terpakai' => $used,
                    'berlangsung' => $this->isScheduleActive($schedule)
                ];
            })
            ->when($isLimitQuota, fn($schedules) => $schedules->where('sisa_kuota', '>', 0));

        return $this;
    }

    /**
     * Check if schedule is currently active
     *
     * @param mixed $schedule
     * @return bool
     */
    private function isScheduleActive($schedule): bool
    {
        // $jamMulai = \Carbon\Carbon::parse($schedule['jam_mulai'])->subMinutes(30);
        // $jamSelesai = \Carbon\Carbon::parse($schedule['jam_selesai']);
        // return now()->isBetween($jamMulai, $jamSelesai);

        return true; // Temporary: always active
    }

    /**
     * Get final collection with additional metadata
     */
    public function get(): Collection
    {

        return $this->schedules
            ->map(fn($schedule) => [
                '_id' => $this->generateScheduleId($schedule),
                ...(is_array($schedule) ? $schedule : $schedule->toArray())
            ]);
    }

    /**
     * Generate unique ID for schedule
     * Cached to avoid repeated encoding
     */
    private function generateScheduleId(array|Schedule $schedule): string
    {
        $key = implode('|', [
            $schedule['kd_poli'] ?? '',
            $schedule['kd_dokter'] ?? '',
            $schedule['hari_kerja'] ?? '',
            $schedule['jam_mulai'] ?? '',
            $schedule['jam_selesai'] ?? ''
        ]);

        return Str::toBase64($key);
    }

    /**
     * Clear all schedule caches
     */
    public static function clearCache(): void
    {
        $today = now()->format('Y-m-d');

        for ($hour = 0; $hour < 24; $hour++) {
            $hourStr = str_pad($hour, 2, '0', STR_PAD_LEFT);
            Cache::forget("schedules_today_{$today}_{$hourStr}");
        }
    }
}
