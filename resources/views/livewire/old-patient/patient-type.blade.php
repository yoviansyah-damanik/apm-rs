<div class="grid grid-cols-2 gap-4" x-data="{
    pending: null,
    resetTimeout: null,
    handleSelect(kdPj, name, desc, isDisabled) {
        if (isDisabled) return;
        if (this.pending === kdPj) {
            this.pending = null;
            clearTimeout(this.resetTimeout);
            $dispatch('speak', { text: 'Anda memilih ' + name + '.' });
            $wire.setPayType(kdPj);
            return;
        }
        this.pending = kdPj;
        $dispatch('speak-stop');
        $dispatch('speak', { text: name + '. ' + desc + '. Ketuk sekali lagi untuk melanjutkan.' });
        clearTimeout(this.resetTimeout);
    }
}"
    @speak-ended.window="if (pending !== null) { clearTimeout(resetTimeout); resetTimeout = setTimeout(() => { pending = null; }, 5000); }">
    @if (!empty($payTypes))
        @foreach ($payTypes as $payType)
            @php
                $isBpjsType = in_array($payType->kd_pj, $defaultBpjsPayTypes);
                $isDisabled = $isBpjsType && !$canUseBpjs;
                $nameLower = strtolower($payType->png_jawab);
                $icon = str_contains($nameLower, 'bpjs')
                    ? 'shield-check'
                    : (str_contains($nameLower, 'jasa')
                        ? 'car'
                        : 'credit-card');
                $desc = match (true) {
                    str_contains($nameLower, 'bpjs kesehatan') && str_contains($nameLower, 'non')
                        => 'Peserta BPJS Kesehatan tidak dalam jaringan BPJS',
                    str_contains($nameLower, 'bpjs kesehatan') => 'Peserta BPJS Kesehatan dengan rujukan aktif',
                    str_contains($nameLower, 'bpjs ketenagakerjaan')
                        => 'Peserta jaminan kecelakaan kerja BPJS Ketenagakerjaan',
                    str_contains($nameLower, 'jasa raharja') => 'Korban kecelakaan lalu lintas Jasa Raharja',
                    default => 'Pasien umum dengan pembayaran mandiri',
                };
            @endphp
            <flux:button variant="ghost" wire:loading.attr="disabled" wire:target="setPayType"
                @click="handleSelect('{{ $payType->kd_pj }}', '{{ addslashes($payType->png_jawab) }}', '{{ addslashes($desc) }}', {{ $isDisabled ? 'true' : 'false' }})"
                ::class="pending === '{{ $payType->kd_pj }}' && 'ring-4 ring-secondary-300 !bg-yellow-400/20 scale-[1.03] shadow-[0_0_24px_4px_rgba(255,247,0,0.35)]'"
                @class([
                    'drop-shadow-xl relative overflow-hidden h-36 flex flex-col rounded-xl justify-center items-center text-center bg-gradient-to-br from-primary-700 to-primary-500 hover:to-yellow-300 active:to-yellow-300 backdrop-blur-2xl backdrop-filter transition-all duration-300',
                    'opacity-50 pointer-events-none' => $isDisabled,
                ])>
                <flux:heading class="!text-3xl uppercase !text-secondary-300 !whitespace-normal text-wrap">
                    {{ $payType->png_jawab }}
                </flux:heading>
                <flux:text class="text-white font-light text-wrap text-sm px-2">
                    @if ($isDisabled)
                        Tidak tersedia
                    @else
                        {{ $desc }}
                    @endif
                </flux:text>

                {{-- Icon background --}}
                <flux:icon :name="$icon"
                    class="absolute size-36 -left-6 -z-10 text-white/10 top-1/2 -translate-y-1/2 h-full rotate-6" />

                {{-- Badge konfirmasi --}}
                <div x-show="pending === '{{ $payType->kd_pj }}'" x-transition:enter="transition ease-out duration-300"
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
    @else
        <div class="col-span-2">
            <flux:callout variant="warning" icon="exclamation-triangle" heading="Data Tidak Tersedia">
                <flux:callout.text>
                    Tidak ada pilihan jenis bayar yang tersedia. Silakan hubungi petugas.
                </flux:callout.text>
            </flux:callout>
        </div>
    @endif

    <div class="col-span-2 text-center text-white/60 text-sm py-1">
        Ketuk tombol <span class="text-secondary-300 font-semibold">1x</span> untuk mendengar informasi &nbsp;·&nbsp; Ketuk
        <span class="text-secondary-300 font-semibold">2x</span> untuk melanjutkan
    </div>
</div>
