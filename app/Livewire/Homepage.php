<?php

namespace App\Livewire;

use Livewire\Component;

class Homepage extends Component
{
    public array $menus = [];

    public function mount()
    {
        $this->menus =
            [
                [
                    "title" => "Antrean Loket",
                    "href" => route('new-patient'),
                    'description' => 'Untuk pasien baru yang belum terdaftar dan pasien lama dengan data tidak sesuai',
                    'icon' => 'pencil-line',
                    'status' => 1
                ],
                [
                    "title" => "Antrean Poli",
                    "href" => route('old-patient'),
                    'description' => 'Lakukan pendaftaran mandiri untuk pasien lama yang sudah terdaftar dengan data yang sesuai',
                    'icon' => 'stethoscope',
                    'status' => 1
                ],
                [
                    "title" => "Check In JKN",
                    "href" => route('check-in'),
                    'description' => 'Lakukan check in mandiri untuk pasien yang telah mendaftar melalui aplikasi Mobile JKN',
                    'icon' => 'fingerprint',
                    'status' => 1
                ],
                [
                    "title" => "Cek Kepesertaan BPJS",
                    "href" => route('participant-checker'),
                    'description' => 'Pastikan anda memiliki status kepesertaan dan rujukan aktif.',
                    'icon' => 'ticket-x',
                    'status' => 1
                ],
                [
                    "title" => "Antrean Farmasi",
                    "href" => route('pharmacy'),
                    'description' => 'Cetak antrean farmasi untuk pasien yang sudah mendapatkan resep dari dokter.',
                    'icon' => 'printer',
                    'status' => 1
                ],
                [
                    "title" => "Jadwal Poliklinik",
                    "href" => route('schedules'),
                    'description' => 'Lihat jadwal poliklinik.',
                    'icon' => 'calendar',
                    'status' => 1
                ],
            ];
    }

    public function render()
    {
        return view('livewire.homepage')
            ->layout('components.layouts.console-box');
    }
}
