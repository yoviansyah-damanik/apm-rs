<?php

namespace App\Livewire\CheckIn;

use Livewire\Component;
use App\Models\Register;
use App\Models\Schedule;
use Illuminate\Support\Str;
use Livewire\Attributes\Reactive;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Finish extends Component
{
    public $jknBooking;
    private $preCheckInMinutes = 30;

    /**
     * Summary of checkInStatus
     * @var int
     * 0 = Belum
     * 1 = Checkin
     * 2 = Batal
     * 3 = Gagal
     */
    public $checkInStatus = 0;

    public function mount()
    {
        $this->preCheckInMinutes = env('PRE_CHECKIN_MINUTES', 30);
        $this->setCheckInStatus();
    }

    public function render()
    {
        return view('livewire.check-in.finish');
    }

    public function setCheckInStatus()
    {
        if ($this->jknBooking['status'] == 'Belum') {
            $this->checkInStatus = 0;
        } elseif ($this->jknBooking['status'] == 'Checkin') {
            $this->checkInStatus = 1;
        } elseif ($this->jknBooking['status'] == 'Batal') {
            $this->checkInStatus = 2;
        } elseif ($this->jknBooking['status'] == 'Gagal') {
            $this->checkInStatus = 3;
        }
    }

    public function refreshStatus()
    {
        $this->jknBooking = $this->jknBooking->refresh();
        $this->setCheckInStatus();
    }

    public function manualCheckIn()
    {
        if ($this->checkCheckInSchedule()) {
            $this->jknBooking->update([
                'status' => 'Checkin',
                'validasi' => now()
            ]);

            $this->refreshStatus();
        } else {
            LivewireAlert::warning()
                ->title("Check In belum dapat dilakukan")
                ->text("Check In dapat dilakukan paling cepat 30 menit sebelum jam mulai praktek.")
                ->timer(0)
                ->show();
        }
    }

    public function checkCheckInSchedule(): bool
    {
        $jamPraktek = $this->jknBooking['jampraktek'];
        $jamMulai = \Carbon\Carbon::parse(explode('-', $jamPraktek)[0])->subMinutes($this->preCheckInMinutes);
        $jamSelesai = \Carbon\Carbon::parse(explode('-', $jamPraktek)[1]);

        return now()->isBetween($jamMulai, $jamSelesai);
    }

    public function backToHome()
    {
        return $this->redirectRoute('home', navigate: true);
    }
}
