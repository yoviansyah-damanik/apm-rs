<?php

namespace App\Livewire;

use App\Helpers\MagicHelper;
use App\Repository\ScheduleRepository;
use Carbon\Carbon;
use Livewire\Component;

class Schedules extends Component
{
    private const DAY_NAME_TO_NUMBER = [
        'AKHAD' => 0,
        'SENIN' => 1,
        'SELASA' => 2,
        'RABU' => 3,
        'KAMIS' => 4,
        'JUMAT' => 5,
        'SABTU' => 6,
    ];

    public int $totalDays = 30;
    public array $excludedDays = [];
    public array $dates = [];
    public string $selectedDate = '';
    public array $schedules = [];

    public function mount(): void
    {
        $this->resolveExcludedDays();
        $this->generateDates();
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadSchedules();

        $this->dispatch('speak', text: 'Informasi Jadwal Poliklinik. Silahkan pilih tanggal dan klik nama poliklinik untuk mendengar informasi jadwal dokter.');
    }

    /** Dipanggil saat user klik tanggal di navigator */
    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->loadSchedules();
    }

    private function resolveExcludedDays(): void
    {
        $activeDayNames = (new ScheduleRepository())->getActiveDayNames();

        $activeDayNumbers = array_filter(
            array_map(fn($name) => self::DAY_NAME_TO_NUMBER[$name] ?? null, $activeDayNames),
            fn($n) => $n !== null,
        );

        $this->excludedDays = array_values(
            array_diff([0, 1, 2, 3, 4, 5, 6], $activeDayNumbers)
        );
    }

    private function generateDates(): void
    {
        $current = Carbon::today();
        $count = 0;
        $maxIterations = $this->totalDays * 7 + 14;

        for ($i = 0; $i < $maxIterations && $count < $this->totalDays; $i++) {
            if (!\in_array($current->dayOfWeek, $this->excludedDays)) {
                $this->dates[] = [
                    'date' => $current->format('Y-m-d'),
                    'day' => MagicHelper::getDaysName($current->dayOfWeek),
                    'label' => $current->locale('id')->isoFormat('ddd, D MMM'),
                    'fullDate' => $current->locale('id')->isoFormat('dddd, D MMMM Y'),
                    'dayNum' => $current->day,
                    'month' => $current->locale('id')->isoFormat('MMM'),
                    'dayName' => $current->locale('id')->isoFormat('ddd'),
                    'isToday' => $current->isToday(),
                ];
                $count++;
            }
            $current = $current->addDay();
        }
    }

    /** Muat jadwal untuk tanggal yang dipilih saja */
    private function loadSchedules(): void
    {
        if (empty($this->selectedDate)) {
            $this->schedules = [];
            return;
        }

        $date = Carbon::parse($this->selectedDate);
        $result = (new ScheduleRepository())->getByDate($date, $date);
        $this->schedules = $result[$this->selectedDate] ?? [];
    }

    public function render()
    {
        $selectedDateInfo = collect($this->dates)->firstWhere('date', $this->selectedDate);

        return view('livewire.schedules', [
            'selectedDateInfo' => $selectedDateInfo,
            'schedules' => $this->schedules,
        ])->layout('components.layouts.console-box');
    }
}
