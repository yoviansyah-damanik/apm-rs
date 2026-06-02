<?php

namespace App\Livewire\Pharmacy;

use App\Models\Register;
use Livewire\Component;
use Livewire\Attributes\On;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Search extends Component
{
    public $idNumber = '';

    public function render()
    {
        return view('livewire.pharmacy.search');
    }

    #[On('numpad-updated')]
    public function checkNumber($payload)
    {
        $this->idNumber = $payload['value'];
    }

    #[On('numpad-entered')]
    public function checkPatient($payload = null): void
    {
        $this->idNumber = $payload['value'];

        if ($this->idNumber == '' || strlen($this->idNumber) != 15) {
            LivewireAlert::title("Format kode booking salah!")
                ->text("Format kode booking berjumlah 15 karakter yang dimulai dari huruf A, B, atau D.")
                ->withConfirmButton() // Enables button with default text
                ->confirmButtonText('OK')
                ->timer(0)
                ->warning()
                ->show();
        } else {
            $register = Register::where('no_rawat', $this->idNumber)
                ->where('stts', '!=', 'Batal')
                ->with([
                    'patient',
                    'payType',
                    'recipe' => fn($q) => $q->whereHas('queue'),
                    'recipe.queue',
                    'recipe.compounds'
                ])
                ->first();

            if ($register) {
                // Validasi apakah ada data resep
                if (empty($register->recipe) || $register->recipe->isEmpty()) {
                    LivewireAlert::title("Data resep tidak ditemukan!")
                        ->text("Pasien dengan kode booking " . $this->idNumber . " belum memiliki resep atau antrean farmasi.")
                        ->withConfirmButton()
                        ->confirmButtonText('OK')
                        ->timer(0)
                        ->warning()
                        ->show();
                } else {
                    $this->dispatch('setRegisterData', $register->toArray());
                    $this->dispatch('setStep');
                }
            } else {
                LivewireAlert::title("Data tidak ditemukan!")
                    ->text("Data dengan kode booking " . $this->idNumber . " tidak ditemukan di sistem.")
                    ->withConfirmButton() // Enables button with default text
                    ->confirmButtonText('OK')
                    ->timer(0)
                    ->warning()
                    ->show();
            }
        }
    }
}
