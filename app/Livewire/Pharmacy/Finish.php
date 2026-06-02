<?php

namespace App\Livewire\Pharmacy;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class Finish extends Component
{
    public $registerData;

    public $data;

    public function mount()
    {
        if (isset($this->registerData['pay_type'])) {
            $this->registerData['patient']['paytype'] = $this->registerData['pay_type'];
        }

        // Pastikan recipe ada dan merupakan array sebelum diiterasi
        if (isset($this->registerData['recipe']) && is_array($this->registerData['recipe'])) {
            foreach ($this->registerData['recipe'] as &$recipe) {
                $recipe['tipe_resep'] = count($recipe['compounds'] ?? []) > 0 ? 'Racikan' : 'Non Racikan';
            }
        }

        $this->data = [
            'patient' => $this->registerData['patient'],
            'no_rawat' => $this->registerData['no_rawat'],
            'tgl_registrasi' => $this->registerData['tgl_registrasi'],
        ];
    }

    public function render()
    {
        return view('livewire.pharmacy.finish');
    }

    public function placeholder()
    {
        return view('placeholders.finish');
    }

    public function backToHome()
    {
        return $this->redirectRoute('home', navigate: true);
    }
}
