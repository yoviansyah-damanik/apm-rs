<?php

return [

    /*
    |--------------------------------------------------------------------------
    | BPJS Credentials
    |--------------------------------------------------------------------------
    |
    | Username dan password untuk autentikasi BPJS Biometric API dan login
    | ke aplikasi FRISTA/Fingerprint.
    |
    */

    'username' => env('BIOMETRIC_USERNAME', ''),
    'password' => env('BIOMETRIC_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | FRISTA Application Path
    |--------------------------------------------------------------------------
    |
    | Path lengkap ke aplikasi FRISTA (.exe file).
    | Contoh: "C:\Program Files (x86)\frista\frista.exe"
    |
    */

    'frista_path' => env('FRISTA_PATH', ''),

    /*
    |--------------------------------------------------------------------------
    | Fingerprint Application Path
    |--------------------------------------------------------------------------
    |
    | Path lengkap ke aplikasi Fingerprint BPJS Kesehatan (.exe file).
    | Contoh: "C:\Program Files (x86)\BPJS Kesehatan\Aplikasi Sidik Jari BPJS Kesehatan\After.exe"
    |
    */

    'fingerprint_path' => env('FINGERPRINT_PATH', ''),

    /*
    |--------------------------------------------------------------------------
    | Auto-Launch Settings
    |--------------------------------------------------------------------------
    |
    | Pengaturan untuk auto-launch aplikasi biometric.
    |
    */

    'auto_launch' => [
        // Aktifkan auto-launch aplikasi saat tombol diklik
        'enabled' => env('BIOMETRIC_AUTO_LAUNCH', true),

        // Aktifkan auto-copy credentials ke clipboard
        'auto_copy_credentials' => env('BIOMETRIC_AUTO_COPY', true),

        // Delay dalam milidetik
        'delays' => [
            'app_launch' => 1000,           // Delay setelah launch aplikasi
            'app_load' => 3000,             // Tunggu aplikasi load sepenuhnya
            'after_login' => 2000,          // Delay setelah submit login
            'after_participant_number' => 1500, // Delay setelah isi nomor peserta
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Application-Specific Settings
    |--------------------------------------------------------------------------
    |
    | Pengaturan khusus untuk masing-masing aplikasi biometric.
    |
    */

    'frista' => [
        // Support command-line arguments untuk auto-login (jika tersedia)
        'supports_cli_args' => false,

        // Format command-line arguments (jika supported)
        'cli_format' => '',  // Contoh: '--username={username} --password={password}'
    ],

    'fingerprint' => [
        // Support command-line arguments untuk auto-login (jika tersedia)
        'supports_cli_args' => false,

        // Format command-line arguments (jika supported)
        'cli_format' => '',  // Contoh: '/u {username} /p {password}'
    ],

];
