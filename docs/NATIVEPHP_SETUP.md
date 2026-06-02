# Dokumentasi Setup NativePHP v2

## Overview
Aplikasi APM RS sekarang menggunakan **NativePHP Desktop v2.0.2** untuk menjalankan aplikasi sebagai desktop application menggunakan Electron.

## Yang Telah Dikonfigurasi

### 1. Package Installation
- ✅ Package `nativephp/desktop` v2.0.2 berhasil terinstall
- ✅ Dependencies: symfony/filesystem, spatie/laravel-package-tools, nativephp/php-bin

### 2. Configuration Files

#### `config/nativephp.php`
Konfigurasi utama NativePHP:
- App version: 1.0.0
- App ID: com.nativephp.app
- Provider: `App\Providers\NativeAppServiceProvider`
- Queue workers: default queue dengan memory 128MB, timeout 60s

#### `app/Providers/NativeAppServiceProvider.php`
Service Provider untuk boot aplikasi NativePHP:
- **Development mode**: Window 1024x768, resizable, dengan menu
- **Production mode** (commented): Fullscreen, always on top, tidak bisa ditutup
- PHP.ini settings: 512MB memory, max execution 300s

### 3. Composer Scripts
Updated untuk NativePHP v2:
```json
"native:dev": [
    "Composer\\Config::disableProcessTimeout",
    "npx concurrently -k -c \"#93c5fd,#c4b5fd\" \"php artisan native:run --no-interaction\" \"npm run dev\" --names=app,vite"
]
```

### 4. Vite Configuration
`vite.config.js` sudah dikonfigurasi untuk:
- Laravel Vite plugin dengan hot reload
- TailwindCSS v4
- CORS enabled
- Strict port: false (agar tidak error jika port sudah digunakan)

## Cara Menjalankan Aplikasi

### Development Mode (Recommended)
```bash
composer run native:dev
```
Command ini akan menjalankan **3 proses sekaligus**:
1. **NativePHP app** (Electron desktop window)
2. **Vite dev server** (Hot reload untuk assets)
3. **Reverb WebSocket** (Real-time broadcasting untuk antrian)

Output akan menampilkan 3 tab berwarna:
- 🔵 **app** - NativePHP Electron
- 🟣 **vite** - Vite dev server
- 🟠 **reverb** - WebSocket server

### Manual Start
Jika ingin menjalankan secara terpisah:

**Terminal 1 - Reverb WebSocket:**
```bash
php artisan reverb:start
```

**Terminal 2 - Vite Dev Server:**
```bash
npm run dev
```

**Terminal 3 - NativePHP:**
```bash
php artisan native:run
```

## Available Commands

### Development
- `php artisan native:run` - Start NativePHP development server (v2 command)
- `php artisan native:serve` - [DEPRECATED] Start development server (gunakan native:run)

### Build & Production
- `php artisan native:build` - Build aplikasi untuk OS tertentu
  - `php artisan native:build --os=win` - Build untuk Windows
  - `php artisan native:build --os=mac` - Build untuk macOS
  - `php artisan native:build --os=linux` - Build untuk Linux
- `php artisan native:publish` - Build dan publish aplikasi

### Database Management
- `php artisan native:migrate` - Run migrations di NativePHP environment
- `php artisan native:migrate:fresh` - Drop tables dan re-run migrations
- `php artisan native:seed` - Seed database
- `php artisan native:db:wipe` - Wipe database

### Utilities
- `php artisan native:install` - Install NativePHP resources
- `php artisan native:reset` - Clear build dan dist files
- `php artisan native:debug` - Generate debug info untuk troubleshooting

## Struktur Window Configuration

### Current Setup (Development)
```php
Window::open('main')
    ->title('APM RS - Aplikasi Pendaftaran Mandiri')
    ->route('home')
    ->icon(public_path('icon.png'))
    ->width(1024)
    ->height(768)
    ->alwaysOnTop(false)
    ->minimizable(true)
    ->maximizable(true)
    ->closable(true)
    ->resizable(true);
```

### Production Setup (Uncomment untuk production)
```php
Window::open('main')
    ->title('APM Service')
    ->hideMenu()
    ->route('home')
    ->icon(public_path('icon.png'))
    ->alwaysOnTop(true)
    ->minimizable(false)
    ->maximizable(false)
    ->closable(false)
    ->fullscreen();
```

## Perbedaan NativePHP v1 vs v2

| Aspek | v1 (Electron) | v2 (Desktop) |
|-------|---------------|--------------|
| Package | `nativephp/electron` | `nativephp/desktop` |
| Namespace | `Native\Laravel\` | `Native\Desktop\` |
| Command | `native:serve` | `native:run` |
| Window API | `Window::open()` | `Window::open('id')` |

## PHP.ini Settings

Aplikasi dikonfigurasi dengan setting berikut:
```php
[
    'memory_limit' => '512M',
    'display_errors' => '0',
    'error_reporting' => 'E_ALL',
    'max_execution_time' => '300',
    'max_input_time' => '300',
]
```

Setting ini cocok untuk aplikasi yang:
- Memerlukan memory besar (512MB)
- Memiliki operasi yang memakan waktu (5 menit max)
- Production-ready (display_errors = 0)

## Database Configuration

### NativePHP Database Setup
Secara default, NativePHP membuat database SQLite terpisah untuk desktop app. Namun aplikasi ini dikonfigurasi untuk **menggunakan MariaDB yang sama** dengan web version.

**Konfigurasi:**
```php
// config/database.php
'nativephp' => [
    'driver' => 'mariadb',
    'host' => env('DB_HOST'),
    'database' => env('DB_DATABASE'),
    // ... same as mariadb connection
]
```

**Keuntungan:**
- ✅ Data sinkron antara web dan desktop app
- ✅ Tidak perlu migrasi database terpisah
- ✅ Langsung akses ke data existing

**File SQLite (Tidak digunakan):**
- `bootstrap/nativephp.sqlite` - Dibuat otomatis tapi tidak dipakai
- `database/nativephp.sqlite` - Dibuat otomatis tapi tidak dipakai

## Troubleshooting

### Issue: "no such table: setting" (Connection: nativephp)
**Error:** `SQLSTATE[HY000]: General error: 1 no such table: setting (Connection: nativephp)`

**Penyebab:** NativePHP mencoba menggunakan SQLite database yang kosong

**Solusi:** ✅ Sudah diperbaiki!
- Connection `nativephp` sekarang pointing ke MariaDB
- Menggunakan database yang sama dengan web version
- Clear cache: `php artisan config:clear`

### Issue: "Command native:php-ini is not defined"
**Error:** `Error: Command failed... native:php-ini is not defined`

**Penyebab:** Interface `ProvidesPhpIni` tidak digunakan di NativePHP v2

**Solusi:** ✅ Sudah diperbaiki!
- Removed `implements ProvidesPhpIni` dari NativeAppServiceProvider
- NativePHP v2 tidak memerlukan interface ini
- PHP configuration handled by Electron build process

**Note:** Error ini tidak mempengaruhi jalannya aplikasi. PHP server tetap start dengan configuration default.

### Issue: "cURL error 56: Recv failure: Connection was reset" (Reverb)
**Error:** `cURL error 56 for http://apm-rs.test:9999/apps/.../events`

**Penyebab:** Laravel Reverb (WebSocket server) tidak berjalan

**Solusi:**
Script `composer run native:dev` sudah include Reverb. Pastikan menjalankan dengan:
```bash
composer run native:dev
```

Script ini akan menjalankan 3 proses:
1. NativePHP app
2. Vite dev server
3. Reverb WebSocket server

**Alternatif - Disable Broadcasting:**
Jika tidak perlu real-time updates, ubah di `.env`:
```env
BROADCAST_CONNECTION=null
```

### Issue: "Class 'Native\Laravel\...' not found"
**Solusi:** Namespace berubah di v2. Pastikan semua import menggunakan `Native\Desktop\` bukan `Native\Laravel\`

### Issue: "native:serve command not found"
**Solusi:** Di v2, gunakan `php artisan native:run` bukan `native:serve`

### Issue: Window tidak muncul
**Solusi:**
1. Pastikan `NativeAppServiceProvider` terdaftar di `config/nativephp.php`
2. Clear config: `php artisan config:clear`
3. Cek log di console

### Issue: Assets tidak load
**Solusi:**
1. Pastikan Vite dev server berjalan
2. Check vite.config.js CORS settings
3. Restart kedua server (native:run dan npm run dev)

### Issue: Port 9999 sudah digunakan
**Solusi:** Ubah port Reverb di `.env`:
```env
REVERB_PORT=8080
REVERB_SERVER_PORT=8080
VITE_REVERB_PORT=8080
```
Lalu restart semua server

## Testing

### Web Browser Test
Aplikasi tetap bisa dijalankan sebagai web app:
```bash
composer run dev
```
Akses: http://localhost:8000

### Desktop App Test
```bash
composer run native:dev
```
Window Electron akan muncul dengan aplikasi di dalamnya

## Production Build

Untuk membuat executable production:

1. **Build untuk Windows:**
```bash
php artisan native:build --os=win
```

2. **Build untuk macOS:**
```bash
php artisan native:build --os=mac
```

3. **Build untuk Linux:**
```bash
php artisan native:build --os=linux
```

File hasil build ada di folder `dist/`

## Security Notes

⚠️ **PENTING untuk Production:**
1. Update `NATIVEPHP_APP_ID` di `.env` dengan ID unik (format: com.company.app)
2. Set `display_errors` = 0 di production
3. Uncomment production window configuration di `NativeAppServiceProvider`
4. Test extensively sebelum deploy

## Support Resources

- [NativePHP Documentation](https://nativephp.com/docs)
- [NativePHP Desktop GitHub](https://github.com/NativePHP/desktop)
- [Electron Documentation](https://www.electronjs.org/docs)

## Version Info

- NativePHP Desktop: v2.0.2
- Laravel: v12.36.0
- PHP: ^8.2
- Electron: (included in nativephp/desktop)

---

**Last Updated:** 2025-11-19
**Setup By:** Claude Code
