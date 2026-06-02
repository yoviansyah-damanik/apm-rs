# Dokumentasi Handling Timeout BPJS

## Overview
Sistem ini sudah dilengkapi dengan mekanisme untuk menangani timeout dari server BPJS. Ketika terjadi timeout atau "Maximum execution time exceeded", aplikasi akan otomatis menampilkan notifikasi dan redirect ke halaman home.

## Komponen yang Telah Dibuat

### 1. Custom Exception: `BpjsTimeoutException`
Location: `app/Exceptions/BpjsTimeoutException.php`

Exception ini akan otomatis di-throw ketika terjadi timeout saat komunikasi dengan server BPJS.

### 2. ApiService (Updated)
Location: `app/Services/ApiService.php`

Semua method di ApiService sudah diupdate untuk mendeteksi timeout dan throw `BpjsTimeoutException` secara otomatis.

## Cara Implementasi

### Untuk Livewire Component

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\BpjsService;
use App\Exceptions\BpjsTimeoutException;

class YourComponent extends Component
{
    public function yourMethod()
    {
        try {
            $bpjsService = new BpjsService($participantNumber);
            $result = $bpjsService->getParticipant();

            // Proses data
            // ...

        } catch (BpjsTimeoutException $e) {
            // Tampilkan notifikasi error
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);

            // Redirect ke home
            return $this->redirect(route('home'), navigate: true);

        } catch (\Exception $e) {
            // Handle exception lainnya
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
```

### Untuk Controller Tradisional

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BpjsService;
use App\Exceptions\BpjsTimeoutException;

class YourController extends Controller
{
    public function yourMethod(Request $request)
    {
        try {
            $bpjsService = new BpjsService($request->participant_number);
            $result = $bpjsService->getParticipant();

            // Proses data
            // ...

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (BpjsTimeoutException $e) {
            // Jika AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 408);
            }

            // Jika regular request, redirect dengan notifikasi
            return redirect()->route('home')
                ->with('error', $e->getMessage());

        } catch (\Exception $e) {
            // Handle exception lainnya
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
```

### Dengan LivewireAlert (Contoh yang sudah diimplementasi)

```php
<?php

namespace App\Livewire\CheckIn;

use Livewire\Component;
use App\Services\BpjsService;
use App\Exceptions\BpjsTimeoutException;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Search extends Component
{
    public function checkPatient()
    {
        try {
            $bpjsService = new BpjsService($this->idNumber);
            $payload = $bpjsService->getParticipant();

            // Proses data
            // ...

        } catch (BpjsTimeoutException $e) {
            LivewireAlert::error()
                ->title('Server BPJS Tidak Dapat Diakses')
                ->text($e->getMessage())
                ->timer(0)
                ->show();

            return $this->redirect(route('home'), navigate: true);

        } catch (\Exception $e) {
            LivewireAlert::error()
                ->title('Terjadi Kesalahan')
                ->text('Error: ' . $e->getMessage())
                ->timer(0)
                ->show();
        }
    }
}
```

## File-file yang Perlu Diupdate

Berikut adalah file-file Livewire yang menggunakan BpjsService dan perlu ditambahkan handling timeout:

1. `app/Livewire/ParticipantChecker/Finish.php`
2. `app/Livewire/ParticipantChecker/Search.php`
3. `app/Livewire/PatientInfo.php`
4. `app/Livewire/OldPatient/Search.php`
5. ✅ `app/Livewire/CheckIn/Search.php` (Sudah diupdate)
6. `app/Livewire/CheckIn/Index.php`
7. `app/Livewire/Elegtability.php`
8. `app/Livewire/CheckIn/Elegtability.php`
9. `app/Livewire/Biometric.php`
10. `app/Livewire/OldPatient/Index.php`
11. `app/Livewire/OldPatient/References.php`
12. `app/Livewire/OldPatient/Biometric.php`
13. `app/Livewire/OldPatient/ControlLetter.php`

## Pesan Error Default

Pesan error default yang akan ditampilkan ketika terjadi timeout:
> "Server BPJS tidak dapat diakses sementara waktu. Harap hubungi petugas."

Anda dapat mengubah pesan ini di file `app/Exceptions/BpjsTimeoutException.php` pada parameter `$message` di constructor.

## Testing

Untuk menguji timeout handling, Anda dapat:

1. Set timeout yang sangat kecil di ApiService (misalnya 1 detik)
2. Atau simulasikan server BPJS yang lambat
3. Atau force throw exception di code untuk testing

```php
// Untuk testing, tambahkan di method yang ingin ditest:
throw new \App\Exceptions\BpjsTimeoutException();
```

## Notes

- Exception ini otomatis log ke file log Laravel
- Response untuk AJAX/API request akan return status code 408 (Request Timeout)
- Regular request akan redirect ke home dengan flash message
- Pastikan route 'home' sudah terdefinisi di routes
