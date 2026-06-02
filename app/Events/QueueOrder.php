<?php

namespace App\Events;

use App\Models\EnrollmentQueue;
use App\Services\QueueService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Event Antrean Loket
 *
 * Diubah dari ShouldBroadcastNow ke ShouldBroadcast untuk performa lebih baik.
 * ShouldBroadcast = Async (via queue) - UX lebih baik, tanpa delay
 * ShouldBroadcastNow = Sync (langsung) - Real-time tapi menyebabkan delay
 *
 * Catatan: Memerlukan queue worker berjalan untuk broadcast async
 * Command: php artisan queue:work
 */
class QueueOrder implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queueNumber;
    public $priorityQueueNumber;
    public $remainingQueue;

    /**
     * Buat instance event baru.
     * Optimasi: kirim data langsung untuk menghindari multiple instantiation QueueService
     */
    public function __construct($queueNumber = null, $priorityQueueNumber = null, $remainingQueue = null)
    {
        $this->queueNumber = $queueNumber;
        $this->priorityQueueNumber = $priorityQueueNumber;
        $this->remainingQueue = $remainingQueue;
    }

    /**
     * Ambil channel dimana event ini akan di-broadcast
     *
     * @return <array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('antripendaftaran')
        ];
    }

    public function broadcastWith(): array
    {
        // Optimasi: gunakan satu instance QueueService jika data tidak diberikan
        if (!$this->queueNumber || !$this->priorityQueueNumber || !$this->remainingQueue) {
            $queueService = new QueueService();
            return [
                'priorityQueueNumber' => $this->priorityQueueNumber ?? $queueService->getNextPriorityQueueNumber(),
                'queueNumber' => $this->queueNumber ?? $queueService->getNextQueueNumber(),
                'remainingQueue' => $this->remainingQueue ?? $queueService->getRemainingQueue(),
                'date' => now()->format('d/m/Y'),
                'timestamp' => now()->toISOString(),
            ];
        }

        return [
            'priorityQueueNumber' => $this->priorityQueueNumber,
            'queueNumber' => $this->queueNumber,
            'remainingQueue' => $this->remainingQueue,
            'date' => now()->format('d/m/Y'),
            'timestamp' => now()->toISOString(),
        ];
    }

    public function broadcastAs()
    {
        return 'antrean-loket.baru';
    }
}
