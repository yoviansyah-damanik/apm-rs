<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    /**
     * Catat log activity
     *
     * @param string $type      bpjs | database | fingerprint | frista | system
     * @param string $event     nama event/aksi
     * @param string $status    success | error
     * @param string $message   pesan log
     * @param array  $context   data tambahan opsional
     */
    public static function log(
        string $type,
        string $event,
        string $status,
        string $message,
        array $context = []
    ): void {
        try {
            ActivityLog::create([
                'type'       => $type,
                'event'      => $event,
                'status'     => $status,
                'message'    => $message,
                'context'    => !empty($context) ? $context : null,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Pastikan kegagalan logging tidak menghentikan proses utama
        }
    }

    public static function success(string $type, string $event, string $message, array $context = []): void
    {
        static::log($type, $event, 'success', $message, $context);
    }

    public static function error(string $type, string $event, string $message, array $context = []): void
    {
        static::log($type, $event, 'error', $message, $context);
    }
}
