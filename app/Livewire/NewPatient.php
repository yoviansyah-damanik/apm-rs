<?php

namespace App\Livewire;

use Livewire\Component;
use App\Events\QueueOrder;
use App\Services\QueueService;
use App\Models\EnrollmentQueue;
use Illuminate\Support\Facades\DB;

class NewPatient extends Component
{
    public string $date;
    public int|string $queueNumber;
    public string $priorityQueueNumber;
    public string $remainingOrder;
    public int|string $lastTakenQueue = 0;
    public bool $isPriority = false;
    public int $queueMode; // 1 = Satu counter (integer), 2 = Regular + Prioritas

    public function mount()
    {
        $this->date = now()->format('d/m/Y');
        $this->queueMode = (int) env('NEW_PATIENT_MODE', 2);

        $queueService = new QueueService();
        $this->queueNumber = $queueService->getNextQueueNumber();
        $this->priorityQueueNumber = $queueService->getNextPriorityQueueNumber();
        $this->remainingOrder = $queueService->getRemainingQueue();

        $this->dispatch('speak', text: 'Selamat datang di Pendaftaran Pasien Baru. Silahkan ambil nomor antrean untuk dilayani di loket pendaftaran.');
    }

    public function render()
    {
        return view('livewire.new-patient')
            ->layout('components.layouts.console-box');
    }

    public function updatequeueNumber($payload)
    {
        if (isset($payload['queueNumber'], $payload['remainingQueue'])) {
            $this->queueNumber = $payload['queueNumber'];
            $this->remainingOrder = $payload['remainingQueue'];

            if (isset($payload['priorityQueueNumber'])) {
                $this->priorityQueueNumber = $payload['priorityQueueNumber'];
            }
        } else {
            $queueService = new QueueService();
            $this->queueNumber = $payload['queueNumber'] ?? $queueService->getNextQueueNumber();
            $this->priorityQueueNumber = $payload['priorityQueueNumber'] ?? $queueService->getNextPriorityQueueNumber();
            $this->remainingOrder = $payload['remainingQueue'] ?? $queueService->getRemainingQueue();
        }
        $this->dispatch('queue-updated', ['queueNumber' => $this->queueNumber]);
    }

    public function takeQueueNumber(): void
    {
        try {
            $today = now()->startOfDay();
            $tomorrow = now()->addDay()->startOfDay();
            $isMode1 = $this->queueMode === 1;

            $newQueue = DB::transaction(function () use ($today, $tomorrow, $isMode1) {
                if ($isMode1) {
                    $lastNumber = EnrollmentQueue::whereBetween('jam', [$today, $tomorrow])
                        ->lockForUpdate()
                        ->max('nomor') ?? 0;

                    $newOrder = ((int) $lastNumber) + 1;

                    return EnrollmentQueue::create([
                        'nomor' => $newOrder,
                        'status' => '0',
                        'jam' => now(),
                        'loket' => null,
                        'dipanggil' => null,
                    ]);
                }

                $lastOrder = EnrollmentQueue::whereBetween('jam', [$today, $tomorrow])
                    ->where('prioritas', false)
                    ->lockForUpdate()
                    ->orderByDesc('nomor')
                    ->value('nomor');

                $newOrder = $lastOrder ? 'B-' . sprintf("%03d", (int) substr($lastOrder, -3) + 1) : 'B-001';

                return EnrollmentQueue::create([
                    'nomor' => $newOrder,
                    'status' => '0',
                    'jam' => now(),
                    'loket' => null,
                    'dipanggil' => null,
                    'prioritas' => false,
                ]);
            });

            $this->lastTakenQueue = $newQueue->nomor;
            $this->isPriority = false;

            if ($isMode1) {
                $this->queueNumber = ((int) $newQueue->nomor) + 1;
            } else {
                $this->queueNumber = 'B-' . sprintf("%03d", (int) substr($newQueue->nomor, -3) + 1);
            }

            QueueService::clearCache();

            $queueService = new QueueService();
            $this->priorityQueueNumber = $queueService->getNextPriorityQueueNumber();
            $this->remainingOrder = $queueService->getRemainingQueue();

            $this->dispatch('queue-taken', [
                'queueNumber' => $newQueue->nomor,
                'message' => "Nomor antrean {$newQueue->nomor} berhasil diambil",
                'autoPrint' => config('app.auto_print_queue', false),
                'printerName' => config('app.queue_printer_name', 'ANTREAN')
            ]);
        } catch (\Exception $e) {
            $this->dispatch('queue-error', [
                'message' => 'Gagal mengambil nomor antrean: ' . $e->getMessage()
            ]);
        }
    }

    public function takePriorityQueueNumber(): void
    {
        try {
            $today = now()->startOfDay();
            $tomorrow = now()->addDay()->startOfDay();

            $newQueue = DB::transaction(function () use ($today, $tomorrow) {
                $lastOrder = EnrollmentQueue::whereBetween('jam', [$today, $tomorrow])
                    ->where('prioritas', true)
                    ->lockForUpdate()
                    ->orderByDesc('nomor')
                    ->value('nomor');

                $newOrder = $lastOrder ? 'A-' . sprintf("%03d", (int) substr($lastOrder, -3) + 1) : 'A-001';

                return EnrollmentQueue::create([
                    'nomor' => $newOrder,
                    'status' => '0',
                    'jam' => now(),
                    'loket' => null,
                    'dipanggil' => null,
                    'prioritas' => true,
                ]);
            });

            $this->lastTakenQueue = $newQueue->nomor;
            $this->isPriority = true;

            $nextPriority = 'A-' . sprintf("%03d", (int) substr($newQueue->nomor, -3) + 1);
            $this->priorityQueueNumber = $nextPriority;

            QueueService::clearCache();

            $queueService = new QueueService();
            $this->queueNumber = $queueService->getNextQueueNumber();
            $this->remainingOrder = $queueService->getRemainingQueue();

            dispatch(function () use ($nextPriority) {
                $queueService = new QueueService();
                broadcast(new QueueOrder(
                    $queueService->getNextQueueNumber(),
                    $nextPriority,
                    $queueService->getRemainingQueue()
                ));
            })->afterResponse();

            $this->dispatch('queue-taken', [
                'queueNumber' => $newQueue->nomor,
                'message' => "Nomor antrean prioritas {$newQueue->nomor} berhasil diambil",
                'autoPrint' => config('app.auto_print_queue', false),
                'printerName' => config('app.queue_printer_name', 'ANTREAN')
            ]);
        } catch (\Exception $e) {
            $this->dispatch('queue-error', [
                'message' => 'Gagal mengambil nomor antrean prioritas: ' . $e->getMessage()
            ]);
        }
    }
}
