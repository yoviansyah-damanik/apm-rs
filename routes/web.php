<?php

use App\Http\Controllers\Placeholder;
use Illuminate\Support\Facades\Route;

// $helperInit = [
//     new \App\Helpers\SettingHelper(),
// ];

// Route::get('placeholder/{placeholderName}', [Placeholder::class, 'index']);

Route::get('/', \App\Livewire\Homepage::class)
    ->name('home');
Route::get('/new-patient', \App\Livewire\NewPatient::class)
    ->name('new-patient');
Route::get('/old-patient', \App\Livewire\OldPatient\Index::class)
    ->name('old-patient');
Route::get('/check-in', \App\Livewire\CheckIn\Index::class)
    ->name('check-in');
Route::get('/participant-checker', \App\Livewire\ParticipantChecker\Index::class)
    ->name('participant-checker');
Route::get('/pharmacy', \App\Livewire\Pharmacy\Index::class)
    ->name('pharmacy');
Route::get('/schedules', \App\Livewire\Schedules::class)
    ->name('schedules');


// Internal tools (tidak ditampilkan di menu)
Route::get('/activity-log', \App\Livewire\ActivityLog::class)
    ->name('activity-log');
Route::get('/function-test', \App\Livewire\FunctionTest::class)
    ->name('function-test');

// Face Verification Routes
// Route::post('/face-verification/verify', [FaceVerificationController::class, 'verify'])
//     ->name('face.verify');


// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

// Route::middleware(['auth'])->group(function () {
//     Route::redirect('settings', 'settings/profile');

//     Route::get('settings/profile', Profile::class)->name('settings.profile');
//     Route::get('settings/password', Password::class)->name('settings.password');
//     Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
// });

// require __DIR__ . '/auth.php';
