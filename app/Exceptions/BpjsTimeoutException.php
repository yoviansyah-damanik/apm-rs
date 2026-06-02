<?php

namespace App\Exceptions;

use Exception;

class BpjsTimeoutException extends Exception
{
    /**
     * Custom exception untuk timeout dari server BPJS
     */
    public function __construct(
        string $message = "Server BPJS tidak dapat diakses sementara waktu. Harap hubungi petugas.",
        int $code = 408,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        // Log exception untuk monitoring
        \Log::error('BPJS Timeout Exception', [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        ]);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render($request)
    {
        // Jika request adalah AJAX atau API
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
            ], 408);
        }

        // Redirect ke home dengan notifikasi
        return redirect()->route('home')
            ->with('error', $this->getMessage());
    }
}
