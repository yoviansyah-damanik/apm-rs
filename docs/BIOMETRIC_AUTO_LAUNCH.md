# Auto-Launch Aplikasi Biometric (FRISTA & Fingerprint)

## Deskripsi

Fitur ini memungkinkan sistem untuk otomatis membuka aplikasi FRISTA dan Fingerprint BPJS Kesehatan, kemudian melakukan automation untuk menyalin kredensial login dan nomor peserta ke clipboard dengan timing yang tepat.

## Fitur Utama

1. **Validasi Path Aplikasi** - Mengecek apakah aplikasi tersedia di path yang dikonfigurasi
2. **Auto-Launch** - Membuka aplikasi secara otomatis saat tombol diklik
3. **Clipboard Automation** - Menyalin username, password, dan nomor peserta ke clipboard secara berurutan
4. **Smart Delays** - Memberikan delay yang tepat antara setiap step untuk memberikan waktu aplikasi load
5. **Browser Notifications** - Menampilkan notifikasi saat kredensial disalin (jika permission diberikan)
6. **Error Handling** - Menangani error dengan graceful fallback

## Konfigurasi

### 1. File .env

Pastikan konfigurasi berikut ada di file `.env`:

```env
# Path ke aplikasi FRISTA
FRISTA_PATH="C:\Program Files (x86)\frista\frista.exe"

# Path ke aplikasi Fingerprint
FINGERPRINT_PATH="C:\Program Files (x86)\BPJS Kesehatan\Aplikasi Sidik Jari BPJS Kesehatan\After.exe"

# Kredensial login
BIOMETRIC_USERNAME="0220R002.dini"
BIOMETRIC_PASSWORD="Rumkit2025*"

# Optional: Auto-launch settings
BIOMETRIC_AUTO_LAUNCH=true
BIOMETRIC_AUTO_COPY=true
```

### 2. Config File

Konfigurasi tambahan dapat diatur di `config/biometric.php`:

```php
'auto_launch' => [
    'enabled' => true,
    'auto_copy_credentials' => true,
    'delays' => [
        'app_launch' => 1000,           // Delay setelah launch aplikasi (ms)
        'app_load' => 3000,             // Tunggu aplikasi load sepenuhnya (ms)
        'after_login' => 2000,          // Delay setelah submit login (ms)
        'after_participant_number' => 1500, // Delay setelah isi nomor peserta (ms)
    ],
],
```

## Cara Penggunaan

### Untuk End User

1. **Akses Halaman Biometric**
   - Navigasi ke halaman validasi biometric dalam flow pendaftaran pasien

2. **Klik Tombol Aplikasi**
   - Klik tombol **"Fingerprint"** untuk membuka aplikasi Fingerprint
   - Atau klik tombol **"Frista"** untuk membuka aplikasi FRISTA

3. **Tunggu Aplikasi Terbuka**
   - Sistem akan otomatis membuka aplikasi
   - Kredensial akan disalin ke clipboard secara berurutan

4. **Paste Kredensial**
   - **Username**: Paste (Ctrl+V) di field username saat notifikasi muncul
   - **Password**: Tunggu beberapa detik, kemudian paste di field password
   - **Nomor Peserta**: Paste di field nomor peserta setelah login

5. **Lihat Notifikasi**
   - Browser akan menampilkan notifikasi saat setiap data disalin
   - Ikuti instruksi pada notifikasi

### Automation Workflow

```
1. Klik tombol "Fingerprint" atau "Frista"
   ↓
2. [1000ms delay] - Aplikasi diluncurkan
   ↓
3. Username disalin ke clipboard → PASTE di field username
   ↓
4. [3000ms delay] - Tunggu aplikasi load
   ↓
5. Password disalin ke clipboard → PASTE di field password
   ↓
6. [2000ms delay] - Tunggu response login
   ↓
7. Nomor Peserta disalin ke clipboard → PASTE di field nomor peserta
   ↓
8. Selesai!
```

## Komponen yang Dibuat

### 1. BiometricLauncher Helper

**File**: `app/Helpers/BiometricLauncher.php`

**Methods**:
- `checkFristaAvailability()` - Cek apakah FRISTA tersedia
- `checkFingerprintAvailability()` - Cek apakah Fingerprint tersedia
- `launchFrista($participantNumber)` - Launch aplikasi FRISTA
- `launchFingerprint($participantNumber)` - Launch aplikasi Fingerprint
- `getCredentials()` - Dapatkan kredensial login
- `getRecommendedDelays()` - Dapatkan delay yang direkomendasikan

### 2. Livewire Component Methods

**File**: `app/Livewire/OldPatient/Form/Biometric.php`

**New Methods**:
- `launchFrista()` - Handler untuk button FRISTA
- `launchFingerprint()` - Handler untuk button Fingerprint
- `getCredentials()` - Getter untuk credentials

### 3. View Updates

**File**: `resources/views/livewire/old-patient/form/biometric.blade.php`

**Changes**:
- Tombol Fingerprint dan FRISTA dengan loading state
- Callout informasi kredensial
- JavaScript automation untuk clipboard dan delays
- Browser notification integration

### 4. Config File

**File**: `config/biometric.php`

**New Configs**:
- `frista_path` - Path ke aplikasi FRISTA
- `fingerprint_path` - Path ke aplikasi Fingerprint
- `auto_launch` - Settings untuk automation

## Troubleshooting

### Aplikasi Tidak Terbuka

**Problem**: Aplikasi tidak terbuka saat tombol diklik

**Solutions**:
1. Cek path aplikasi di `.env` sudah benar
2. Pastikan file .exe ada di path tersebut
3. Cek permission file .exe (bisa dijalankan)
4. Lihat log error di browser console atau Laravel log

### Kredensial Tidak Tersalin

**Problem**: Kredensial tidak tersalin ke clipboard

**Solutions**:
1. Berikan permission clipboard ke browser
2. Cek browser console untuk error
3. Gunakan fallback manual copy dari callout info

### Notifikasi Tidak Muncul

**Problem**: Browser notification tidak muncul

**Solutions**:
1. Berikan permission notification ke browser
2. Cek browser settings → Notifications
3. Kredensial tetap tersalin meskipun notifikasi tidak muncul

### Timing Tidak Tepat

**Problem**: Delay terlalu cepat/lambat untuk aplikasi load

**Solutions**:
1. Sesuaikan delay di `config/biometric.php`
2. Tingkatkan `app_load` jika aplikasi lambat load
3. Tingkatkan `after_login` jika perlu waktu lebih lama

## Keamanan

1. **Password Masking** - Password di UI ditampilkan sebagai bullets (••••)
2. **Env Protection** - Kredensial disimpan di `.env` yang tidak ter-commit ke git
3. **Clipboard Security** - Kredensial hanya disalin saat user klik tombol
4. **No Auto-Fill Form** - Tidak auto-fill form aplikasi eksternal (security limitation)

## Limitasi

1. **Desktop Only** - Hanya bekerja untuk aplikasi desktop (.exe)
2. **Windows Only** - Saat ini hanya support Windows OS
3. **Manual Paste Required** - User harus manual paste kredensial (tidak bisa full automation)
4. **No Direct Form Fill** - Tidak bisa langsung isi form aplikasi eksternal karena security browser
5. **Same Machine** - Aplikasi harus terinstall di mesin yang sama dengan web server

## Future Enhancements

1. **Command-Line Arguments** - Support auto-login via CLI args jika aplikasi support
2. **Windows UI Automation** - Integrasi dengan Windows UI Automation untuk auto-fill penuh
3. **Custom URL Protocol** - Register custom protocol (`frista://`, `fingerprint://`) untuk launch
4. **Multi-Platform** - Support macOS dan Linux jika ada versi aplikasi
5. **Advanced Timing** - Smart timing detection berdasarkan window state

## Support

Untuk bantuan lebih lanjut, hubungi:
- Developer: Yoviansyah Rizki Pratama
- Email: @yoviansyah_damanik
- Website: rumkittnipsp.com

---

Generated dengan Claude Code
