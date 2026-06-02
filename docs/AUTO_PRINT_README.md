# Auto-Print Setup (Tanpa NativePHP)

## Gambaran Umum

Sistem auto-print telah dikonfigurasi untuk secara otomatis membuka print dialog setelah user mengambil nomor antrian, tanpa memerlukan NativePHP atau aplikasi desktop tambahan.

## Cara Kerja

### 1. Flow Auto-Print

```
User klik "Ambil Antrean"
    ↓
Livewire: takeQueueNumber() / takePriorityQueueNumber()
    ↓
Database: Simpan nomor antrian baru (contoh: B-005)
    ↓
Livewire: Set $lastTakenQueue = "B-005"
    ↓
Livewire: Broadcast event ke semua client via Reverb
    ↓
Livewire: Dispatch event 'queue-taken' dengan autoPrint=true
    ↓
JavaScript: Event listener tangkap event
    ↓
JavaScript: Delay 500ms untuk tunggu UI update
    ↓
JavaScript: printArea() trigger browser print dialog
    ↓
User: Tekan Enter / OK untuk print
```

### 2. Komponen yang Terlibat

**Backend (Laravel):**
- `app/Livewire/NewPatient.php` - Component utama
- `app/Events/QueueOrder.php` - Broadcasting event
- `config/app.php` - Konfigurasi printer

**Frontend:**
- `resources/js/print.js` - Auto-print logic
- `resources/views/livewire/new-patient.blade.php` - UI

## Konfigurasi

### File `.env`

```env
# Broadcasting (Reverb)
BROADCAST_CONNECTION=reverb
REVERB_HOST=apm-rs.test          # ⚠️ HOSTNAME ONLY (no http:// or https://)
REVERB_PORT=8080
REVERB_SCHEME=http

# Printer Configuration
QUEUE_PRINTERS_NAME="ANTRIAN-RST"
AUTO_PRINT_QUEUE=true
```

⚠️ **PENTING:** `REVERB_HOST` harus hostname saja TANPA protocol (`http://` atau `https://`). Jika ada protocol, WebSocket connection akan gagal.

### Toggle Auto-Print

**Untuk Mengaktifkan:**
```env
AUTO_PRINT_QUEUE=true
```

**Untuk Menonaktifkan (Mode Manual):**
```env
AUTO_PRINT_QUEUE=false
```

## Perbedaan Mode

### Mode Auto-Print (AUTO_PRINT_QUEUE=true)
- User klik tombol "Ambil Antrean"
- Print dialog **otomatis muncul**
- User hanya perlu tekan **Enter** untuk print
- Lebih cepat dan efisien untuk kiosk

### Mode Manual (AUTO_PRINT_QUEUE=false)
- User klik tombol "Ambil Antrean"
- User harus klik tombol lagi untuk print
- Cocok untuk testing atau admin

## Limitasi Browser

⚠️ **Penting:** Browser modern tidak mengizinkan "silent printing" (print tanpa dialog) karena alasan keamanan.

### Yang Bisa Dilakukan:
✅ Auto-trigger print dialog (user masih perlu confirm)
✅ Auto-select printer (jika user set default printer)
✅ Pre-configure paper size (80mm thermal)

### Yang TIDAK Bisa Dilakukan (Tanpa Software Tambahan):
❌ Print langsung tanpa dialog
❌ Select printer spesifik via JavaScript
❌ Print ke multiple printer sekaligus

## Opsi untuk True Silent Printing

Jika Anda membutuhkan print benar-benar otomatis tanpa dialog:

### Opsi 1: Browser Kiosk Mode (Recommended untuk Kiosk)
```bash
# Chrome Kiosk Mode
chrome.exe --kiosk --kiosk-printing "http://apm-rs.test"
```

### Opsi 2: Print Server (Node.js)
Implementasi print server terpisah menggunakan:
- `node-printer` package
- REST API endpoint untuk print
- Direct communication dengan printer

### Opsi 3: NativePHP (Yang Sebelumnya)
- Build desktop app dengan NativePHP
- Akses langsung ke printer sistem
- Lebih kompleks tapi full control

## Printer yang Didukung

Sistem dikonfigurasi untuk thermal printer 80mm:
- Setting `QUEUE_PRINTERS_NAME` di `.env`
- Paper size: 80mm width, auto height
- Format: ESC/POS compatible

## Testing

### 1. Test Auto-Print
```bash
# Pastikan Reverb berjalan
php artisan reverb:start

# Buka browser
http://apm-rs.test

# Klik "Ambil Antrean"
# Print dialog harus muncul otomatis
```

### 2. Check Console Log
Buka Developer Tools > Console, lihat:
```
Auto-print handler initialized
Auto-print triggered: B-001 -> antrean-loket (Printer: ANTRIAN-RST)
```

### 3. Test Broadcast
Buka 2 browser window, ambil antrian di salah satu, kedua window harus update real-time.

## Troubleshooting

### WebSocket Connection Failed
**Error:** `WebSocket connection to 'ws://http//apm-rs.test:8080' failed`

**Penyebab:** `REVERB_HOST` berisi protocol (`http://` atau `https://`)

**Solusi:**
```env
# ❌ SALAH
REVERB_HOST="${APP_URL}"          # Menghasilkan ws://http://...
REVERB_HOST=http://apm-rs.test    # Menghasilkan ws://http://...

# ✅ BENAR
REVERB_HOST=apm-rs.test           # Menghasilkan ws://apm-rs.test:8080
```

Setelah ubah:
```bash
npm run build
php artisan config:clear
# Restart Reverb server (Ctrl+C lalu php artisan reverb:start)
```

### Print Dialog Tidak Muncul
1. Check `.env`: `AUTO_PRINT_QUEUE=true`
2. Check console untuk error
3. Clear config cache: `php artisan config:clear`
4. Rebuild assets: `npm run build`
5. Hard refresh browser: `Ctrl+Shift+R`

### Nomor Antrian Salah
1. Check Livewire wire:key untuk prevent duplicate
2. Clear cache: `php artisan cache:clear`
3. Restart Reverb server

### Broadcast Tidak Working
1. Check `.env`: `BROADCAST_CONNECTION=reverb`
2. Check Reverb running: `php artisan reverb:start`
3. Check port 8080 tidak dipakai aplikasi lain
4. Check WebSocket connection di browser console (F12)

## Commands Berguna

```bash
# Clear semua cache
php artisan optimize:clear

# Restart Reverb
php artisan reverb:restart

# Build assets
npm run build

# Development watch
npm run dev
```

## Next Steps (Opsional)

### Untuk Silent Printing Penuh:
1. Setup Chrome Kiosk Mode di mesin kiosk
2. Set default printer di OS
3. Enable auto-print di browser settings
4. Test di production environment

### Untuk Print Server:
1. Setup Node.js print server
2. Install node-printer package
3. Buat REST API endpoint
4. Integrate dengan Laravel

---

**Catatan:** Implementasi saat ini sudah optimal untuk web-based system. Print dialog akan muncul otomatis, user hanya perlu 1x tekan Enter untuk print.
