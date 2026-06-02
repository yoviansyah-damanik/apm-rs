<div class="flex flex-col gap-4">
    <div class="flex-1">
        {{-- Status Check In --}}
        <flux:callout :icon="$checkInStatus == 1 ? 'check-circle' : 'x-circle'"
            :color="$checkInStatus == 1 ? 'green' : 'rose'" heading="Status Check In" inline>
            <flux:callout.text>
                <div class="space-y-2">
                    <p>
                        @if ($checkInStatus == 0)
                            Silahkan Check In terlebih dahulu.
                        @elseif($checkInStatus == 1)
                            Check In berhasil. Silakan menuju Poli untuk menunggu antrean dipanggil.
                        @elseif($checkInStatus == 2)
                            Rawatan anda dinyatakan Batal. Silahkan lakukan pendaftaran ulang melalui Mobile JKN
                            atau hubungi petugas.
                        @else
                            Status Check In belum tersedia. Silakan refresh untuk memperbarui status.
                        @endif
                    </p>
                </div>
            </flux:callout.text>
            <x-slot name="actions">
                <flux:button variant="primary" :color="$checkInStatus == 1 ? 'green' : 'rose'" wire:click="refreshStatus"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="refreshStatus">Refresh</span>
                    <span wire:loading wire:target="refreshStatus">
                        <flux:icon.loading class="size-5" />
                    </span>
                </flux:button>
            </x-slot>
        </flux:callout>
    </div>

    @if ($checkInStatus == 0)
        <div class="w-full flex mx-auto gap-4 justify-center items-center bg-white p-4 rounded-2xl shadow-2xl">
            <img src="{{ Vite::image('qrcode-jkn.png') }}" class="w-[300px] mx-auto" lazy />
            <div class="flex-1">
                <div class="text-center mt-2">
                    Silahkan lakukan scan QR Code di atas pada aplikasi Mobile JKN untuk melanjutkan proses Check In.
                </div>
                {{-- Tampilkan selama beberapa detik --}}
                <div x-data="{
                    show: false,
                    durasiDetik: 5,
                    countdown: 5,
                    interval: null,
                    init() {
                        this.countdown = this.durasiDetik;
                        // Update countdown setiap detik
                        this.interval = setInterval(() => {
                            this.countdown--;
                            if (this.countdown <= 0) {
                                clearInterval(this.interval);
                                this.show = true;
                            }
                        }, 1000);
                    }
                }">
                    {{-- Countdown saat belum ditampilkan --}}
                    <div x-show="!show" class="my-4">
                        <div class="text-center text-sm py-2">
                            Check In Manual dalam <span x-text="countdown"></span> detik...
                        </div>
                    </div>

                    {{-- Konten asli setelah countdown selesai --}}
                    <div x-show="show">
                        <flux:separator text="atau" class="my-4" />
                        <flux:button variant="primary" size="xl" class="w-full" wire:click="manualCheckIn">
                            Check In Manual
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @elseif($checkInStatus == 1)
        <div class="space-y-4">
            <flux:callout icon="check-circle" variant="success" heading="Informasi Antrean" inline>
                <flux:callout.text>
                    <div class="space-y-2">
                        <p>
                            Proses Check In Anda telah berhasil. Silahkan menunggu antrean dipanggil. Pastikan Anda
                            berada di area poli yang dituju. Nomor antrean anda sesuai yang ada pada aplikasi Mobile
                            JKN.
                        </p>
                    </div>
                </flux:callout.text>
            </flux:callout>
            <x-queue-number type="poli" :queueNumber="$jknBooking['nomorantrean']" :date="now()->format('Y-m-d')" :data="[
                'nm_pasien' => $this->jknBooking->patient->nm_pasien,
                'jk' => $this->jknBooking->patient->jk,
                'no_rkm_medis' => $this->jknBooking->norm,
                'nm_dokter' => $this->jknBooking->register->doctor->nm_dokter,
                'estimasi_dilayani' => \Carbon\Carbon::createFromTimestamp($this->jknBooking->estimasidilayani)->format(
                    'H:i',
                ),
                'png_jawab' => $this->jknBooking->register->payType->png_jawab,
            ]" />
        </div>
    @endif

    @if ($checkInStatus != 0)
        <flux:button size="2xl" variant="primary" wire:click="backToHome"
            class="h-24 !text-3xl w-full print:hidden">
            Kembali ke Awal
        </flux:button>
    @endif
</div>
