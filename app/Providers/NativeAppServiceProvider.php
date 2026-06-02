<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Window;
use Native\Desktop\Facades\MenuBar;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Boot aplikasi NativePHP
     * Dijalankan saat aplikasi desktop dimulai
     */
    public function boot(): void
    {
        // Override APP_URL agar @vite menghasilkan URL yang benar (http://127.0.0.1:PORT)
        if (!app()->runningInConsole()) {
            $port = $_SERVER['SERVER_PORT'] ?? null;
            if ($port) {
                URL::forceRootUrl("http://127.0.0.1:{$port}");
            }
        }

        Window::open('main')
            ->title('APM RS - Aplikasi Pendaftaran Mandiri')
            ->route('home')
            ->icon(resource_path('images/logo.png'))
            ->fullscreen()
            ->titleBarHidden()
            ->alwaysOnTop(false)
            ->minimizable(false)
            ->maximizable(false)
            ->closable(false)
            ->resizable(false)
            ->movable(false)
            ->maximized();

        MenuBar::create()
            ->route('home');
    }

    /**
     * Konfigurasi php.ini untuk runtime PHP NativePHP
     */
    public function phpIni(): array
    {
        return [
            'memory_limit' => '512M',
            'display_errors' => '1',
            'error_reporting' => 'E_ALL',
            'max_execution_time' => '0',
            'max_input_time' => '0',
            // Diperlukan agar php_pdo_mysql.dll dapat dimuat dari custom PHP package
            'extension_dir' => 'ext',
        ];
    }
}
