<div class="grid grid-cols-2 gap-4" x-data="{
    pending: null,
    resetTimeout: null,
    handleSelect(id, poli, dokter, hari, jam, kuota, sisa) {
        if (this.pending === id) {
            this.pending = null;
            clearTimeout(this.resetTimeout);
            $dispatch('speak', { text: 'Anda memilih poliklinik ' + poli + '. Dokter ' + dokter + '.' });
            $wire.setSchedule(id);
            return;
        }
        this.pending = id;
        $dispatch('speak-stop');
        $dispatch('speak', { text: poli + '. ' + dokter + '. Sisa kuota ' + sisa + ' dari ' + kuota + '. Ketuk sekali lagi untuk melanjutkan.' });
        clearTimeout(this.resetTimeout);
    }
}"
    @speak-ended.window="if (pending !== null) { clearTimeout(resetTimeout); resetTimeout = setTimeout(() => { pending = null; }, 15000); }">
    @if (!empty($schedules) && count($schedules) > 0)
        @foreach ($schedules as $schedule)
            @php
                $isDisabled = !$schedule['berlangsung'] || $schedule['sisa_kuota'] == 0;
                $jam =
                    \Carbon\Carbon::parse($schedule['jam_mulai'])->format('H:i') .
                    ' - ' .
                    \Carbon\Carbon::parse($schedule['jam_selesai'])->format('H:i');
                $scheduleId = $schedule['_id'];
                $nmPoli = addslashes($schedule['polyclinic']['nm_poli']);
                $nmDokter = addslashes($schedule['doctor']['nm_dokter']);
                $hariKerja = $schedule['hari_kerja'];
                $kuota = $schedule['kuota'];
                $sisaKuota = $schedule['sisa_kuota'];
                $clickAction = $isDisabled
                    ? ''
                    : "handleSelect('{$scheduleId}', '{$nmPoli}', '{$nmDokter}', '{$hariKerja}', '{$jam}', '{$kuota}', '{$sisaKuota}')";
            @endphp
            <flux:button variant="ghost" wire:loading.attr="disabled" wire:target="setSchedule" @click="{{ $clickAction }}"
                ::class="pending === '{{ $schedule['_id'] }}' && 'ring-4 ring-secondary-300 !bg-yellow-400/20 scale-[1.03] shadow-[0_0_24px_4px_rgba(255,247,0,0.35)]'"
                @class([
                    'drop-shadow-xl relative overflow-hidden h-32 flex flex-col rounded-xl justify-center items-center text-center bg-gradient-to-br from-primary-700 to-primary-500 hover:to-yellow-300 active:to-yellow-300 backdrop-blur-2xl backdrop-filter transition-all duration-300',
                    'opacity-40 pointer-events-none' => $isDisabled,
                ])>
                {{-- Konten --}}
                <div class="w-full px-2 space-y-0.5">
                    <p class="text-2xl font-black uppercase text-secondary-300 leading-tight tracking-wide">
                        {{ $schedule['polyclinic']['nm_poli'] }}
                    </p>
                    <p class="text-base text-white font-light truncate">
                        {{ $schedule['doctor']['nm_dokter'] }}
                    </p>
                    <div class="flex items-center justify-center flex-wrap gap-x-3 text-sm text-white/80">
                        <span class="font-bold">{{ $schedule['hari_kerja'] }}</span>
                        <span @class([
                            'font-bold',
                            'text-secondary-300' => $schedule['berlangsung'],
                            'text-red-300' => !$schedule['berlangsung'],
                        ])>{{ $jam }}</span>
                        @if (!$schedule['berlangsung'])
                            <span class="font-bold text-red-300">Poli Tutup</span>
                        @elseif ($schedule['sisa_kuota'] == 0)
                            <span class="font-bold text-red-300">Kuota Habis</span>
                        @else
                            <span>Kapasitas: <strong
                                    class="text-secondary-300">{{ $schedule['sisa_kuota'] . '/' . $schedule['kuota'] }}</strong></span>
                        @endif
                    </div>
                </div>

                {{-- Icon background --}}
                <flux:icon.calendar-days
                    class="absolute size-36 -left-6 -z-10 text-white/10 top-1/2 -translate-y-1/2 h-full rotate-6" />

                {{-- Badge konfirmasi --}}
                <div x-show="pending === '{{ $schedule['_id'] }}'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-3 scale-90"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute bottom-2 left-0 right-0 flex justify-center pointer-events-none">
                    <span
                        class="inline-flex items-center gap-1.5 bg-secondary-300 text-primary-900 text-xs font-bold px-3 py-1 rounded-full animate-bounce shadow-lg">
                        <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M13 9l3 3-3 3M5 12h12" />
                        </svg>
                        Ketuk lagi untuk lanjut
                    </span>
                </div>
            </flux:button>
        @endforeach

        <div class="text-center text-white/60 text-sm py-1 col-span-2">
            Ketuk tombol <span class="text-secondary-300 font-semibold">1x</span> untuk mendengar informasi &nbsp;·&nbsp;
            Ketuk <span class="text-secondary-300 font-semibold">2x</span> untuk melanjutkan
        </div>
    @else
        <flux:callout variant="warning" icon="exclamation-triangle" heading="Jadwal Tidak Ditemukan" class="col-span-2">
            <flux:callout.text>
                Tidak ditemukan jadwal untuk hari ini. Silakan hubungi petugas untuk informasi lebih lanjut.
            </flux:callout.text>
        </flux:callout>
    @endif
</div>
