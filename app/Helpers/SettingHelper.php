<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingHelper
{
    private static ?array $data = null;

    private static function init(): void
    {
        if (static::$data !== null) {
            return;
        }

        $settings = DB::connection('simrs')->table('setting')->first();

        if (!$settings) {
            static::$data = [
                'hospitalName' => '',
                'hospitalAddress' => '',
                'hospitalContact' => '',
                'hospitalEmail' => '',
                'hospitalBpjsCode' => '',
                'queuePrintersName' => env('QUEUE_PRINTERS_NAME', ''),
                'sepPrintersName' => env('SEP_PRINTERS_NAME', '')
            ];
            return;
        }

        static::$data = [
            'hospitalName' => $settings->nama_instansi,
            'hospitalAddress' => $settings->alamat_instansi . ', ' . $settings->kabupaten . ', ' . $settings->propinsi,
            'hospitalContact' => $settings->kontak,
            'hospitalEmail' => $settings->email,
            'hospitalBpjsCode' => $settings->kode_ppk,
            'queuePrintersName' => env('QUEUE_PRINTERS_NAME', ''),
            'sepPrintersName' => env('SEP_PRINTERS_NAME', '')
        ];
    }

    public static function get(string $key): ?string
    {
        static::init();

        return static::$data[$key] ?? null;
    }

    public static function getExcludePolyclinics(): array
    {
        $excludes = env('EXCLUDE_POLYCLINICS', []);

        if (!is_array($excludes)) {
            $decoded = json_decode(str_replace("'", '"', $excludes), true);
            $excludes = is_array($decoded) ? $decoded : [];
        }

        return $excludes;
    }

    public static function getExcludePayTypes(): array
    {
        $excludes = env('EXCLUDE_PAY_TYPES', []);

        if (!is_array($excludes)) {
            $decoded = json_decode(str_replace("'", '"', $excludes), true);
            $excludes = is_array($decoded) ? $decoded : [];
        }

        return $excludes;
    }
}
