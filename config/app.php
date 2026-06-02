<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Hospital Name
    |--------------------------------------------------------------------------
    | Digunakan untuk ditampilkan di berbagai tempat dalam aplikasi, seperti header, tiket antrean, dan dokumen lainnya.
    | Pastikan untuk mengatur nilai ini di file .env agar mudah diubah tanpa perlu
    | mengubah kode sumber. Contoh: HOSPITAL_NAME="Rumah Sakit Tk. IV 01.07.03 Padangsidimpuan"
     */

    'hospital_name' => env('HOSPITAL_NAME', 'Nama Rumah Sakit'),

    /*
    |--------------------------------------------------------------------------
    | Voice Hospital Name
    |--------------------------------------------------------------------------
    | Nama rumah sakit yang akan diucapkan oleh fitur text-to-speech. Disarankan untuk menggunakan format yang lebih panjang dan jelas untuk memastikan pengucapan yang benar. Contoh: VOICE_HOSPITAL_NAME="Rumah Sakit Tingkat IV Nol Satu Nol Tujuh Nol Tiga Padangsidimpuan"
    */

    'voice_hospital_name' => env('VOICE_HOSPITAL_NAME', 'Nama Rumah Sakit'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'id'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Printer Configuration
    |--------------------------------------------------------------------------
    |
    | These configuration options determine printer settings for queue tickets
    | and other documents. The auto_print_queue option enables automatic
    | printing of queue tickets without manual intervention.
    |
    */

    'queue_printer_name' => env('QUEUE_PRINTERS_NAME', 'ANTREAN'),
    'sep_printer_name' => env('SEP_PRINTERS_NAME', 'ANTREAN-RST'),
    'auto_print_queue' => env('AUTO_PRINT_QUEUE', false),

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
