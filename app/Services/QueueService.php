<?php

namespace App\Services;

use App\Models\EnrollmentQueue;

class QueueService
{
    private static ?array $todayQueueCache = null;
    private static ?string $cacheDate = null;

    public function __construct()
    {
    }

    private function getMode(): int
    {
        return (int) env('NEW_PATIENT_MODE', 2);
    }

    /**
     * Ambil semua data antrean hari ini dengan caching (satu query)
     */
    private function getTodayQueueData(): array
    {
        $today = now()->format('Y-m-d');

        if (self::$cacheDate !== $today) {
            self::$todayQueueCache = null;
            self::$cacheDate = $today;
        }

        if (self::$todayQueueCache !== null) {
            return self::$todayQueueCache;
        }

        if ($this->getMode() === 1) {
            $queues = EnrollmentQueue::whereDate('jam', now())
                ->select('nomor', 'status')
                ->get();

            self::$todayQueueCache = [
                'lastNumber' => $queues->max('nomor') ?? 0,
                'remainingCount' => $queues->where('status', '0')->count(),
            ];
        } else {
            $queues = EnrollmentQueue::whereDate('jam', now())
                ->select('nomor', 'prioritas', 'status')
                ->get();

            $regularQueues = $queues->where('prioritas', false);
            $priorityQueues = $queues->where('prioritas', true);

            self::$todayQueueCache = [
                'lastRegular' => $regularQueues->sortByDesc('nomor')->first(),
                'lastPriority' => $priorityQueues->sortByDesc('nomor')->first(),
                'remainingCount' => $queues->where('status', '0')->count(),
            ];
        }

        return self::$todayQueueCache;
    }

    /**
     * Ambil nomor antrean berikutnya
     * Mode 1: integer (1, 2, 3...) | Mode 2: string B-001, B-002...
     */
    public function getNextQueueNumber(): int|string
    {
        $data = $this->getTodayQueueData();

        if ($this->getMode() === 1) {
            return ($data['lastNumber'] ?? 0) + 1;
        }

        $lastQueue = $data['lastRegular'];
        if (!$lastQueue) {
            return 'B-001';
        }

        $lastNumber = (int) substr($lastQueue->nomor, -3);
        return 'B-' . sprintf("%03d", $lastNumber + 1);
    }

    /**
     * Ambil nomor antrean prioritas berikutnya (hanya Mode 2)
     */
    public function getNextPriorityQueueNumber(): string
    {
        $data = $this->getTodayQueueData();
        $lastQueue = $data['lastPriority'] ?? null;

        if (!$lastQueue) {
            return 'A-001';
        }

        $lastNumber = (int) substr($lastQueue->nomor, -3);
        return 'A-' . sprintf("%03d", $lastNumber + 1);
    }

    /**
     * Ambil jumlah antrean yang tersisa
     */
    public function getRemainingQueue(): int
    {
        $data = $this->getTodayQueueData();
        return $data['remainingCount'];
    }

    /**
     * Bersihkan cache — panggil setelah membuat antrean baru
     */
    public static function clearCache(): void
    {
        self::$todayQueueCache = null;
    }
}
