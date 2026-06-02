<div class="h-full flex items-center gap-6 print:!block" x-data="{
    activeBlock: null,
    resetTimeout: null,
    handleBlock(block, voiceText) {
        if (this.activeBlock === block) {
            this.activeBlock = null;
            clearTimeout(this.resetTimeout);
            return true;
        }
        this.activeBlock = block;
        $dispatch('speak', { text: voiceText + '. Tekan sekali lagi untuk mengambil antrean.' });
        clearTimeout(this.resetTimeout);
        return false;
    }
}"
    @speak-ended.window="if (activeBlock !== null) { clearTimeout(resetTimeout); resetTimeout = setTimeout(() => { activeBlock = null; }, 5000); }">

    {{-- Area print untuk nomor yang baru diambil (Regular) --}}
    @if ($lastTakenQueue && !$isPriority)
        <div class="hidden" data-area="print" data-printable="antrean-loket" data-page-type="antrean">
            <x-queue-number :queueNumber="$lastTakenQueue" :date="$date" type="loket" />
        </div>
    @endif

    {{-- Area print untuk nomor yang baru diambil (Prioritas) --}}
    @if ($lastTakenQueue && $isPriority)
        <div class="hidden" data-area="print" data-printable="antrean-loket-prioritas" data-page-type="antrean">
            <x-queue-number :queueNumber="$lastTakenQueue" :date="$date" type="loket" />
        </div>
    @endif

    {{-- Panel Kiri: Panduan --}}
    <div class="w-2/5 flex flex-col justify-center py-2 print:hidden">
        <div
            class="h-full flex flex-col justify-center gap-6 backdrop-blur-md bg-primary-900/75 rounded-2xl px-8 py-8 text-white ring-1 ring-white/10 shadow-xl">

            {{-- Brand accent --}}
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-2xl bg-secondary-300/15 flex items-center justify-center ring-2 ring-secondary-300/30 shrink-0">
                    <svg class="w-8 h-8 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-secondary-300 font-bold text-2xl uppercase tracking-widest leading-tight">
                        Antrean
                        Loket</div>
                    <div class="text-primary-300 text-sm">Pendaftaran Pasien Baru</div>
                </div>
            </div>

            <div class="border-t border-white/10"></div>

            {{-- Jenis antrean --}}
            <div class="space-y-4">
                <div class="text-primary-300 text-xs font-semibold uppercase tracking-widest">Jenis Antrean</div>

                <div class="space-y-2.5">
                    @if ($queueMode === 1)
                        <div class="flex items-center gap-3 rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                            <div
                                class="w-10 h-8 rounded-lg bg-secondary-300/20 flex items-center justify-center shrink-0 text-secondary-300 font-bold text-sm">
                                L
                            </div>
                            <div>
                                <div class="font-semibold text-sm">Antrean Loket</div>
                                <div class="text-primary-300 text-xs mt-0.5">Semua pasien pendaftaran</div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-3 rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                            <div
                                class="w-10 h-8 rounded-lg bg-secondary-300/20 flex items-center justify-center shrink-0 text-secondary-300 font-bold text-sm">
                                R
                            </div>
                            <div>
                                <div class="font-semibold text-sm">Regular</div>
                                <div class="text-primary-300 text-xs mt-0.5">Pasien baru & lama (perubahan data)</div>
                            </div>
                        </div>

                        @if ($queueMode >= 2)
                            <div class="flex items-center gap-3 rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                                <div
                                    class="w-10 h-8 rounded-lg bg-secondary-300/20 flex items-center justify-center shrink-0 text-secondary-300 font-bold text-sm">
                                    P
                                </div>
                                <div>
                                    <div class="font-semibold text-sm">Prioritas</div>
                                    <div class="text-primary-300 text-xs mt-0.5">Lansia, disabilitas & ibu hamil</div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <div class="border-t border-white/10"></div>

            {{-- Langkah-langkah --}}
            <div class="space-y-4">
                <div class="text-primary-300 text-xs font-semibold uppercase tracking-widest">Cara Penggunaan</div>

                <div class="flex items-start gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-secondary-300 text-primary-900 font-bold text-sm flex items-center justify-center shrink-0 shadow-lg shadow-secondary-300/20">
                        1</div>
                    <div class="pt-0.5">
                        <div class="font-semibold text-sm">Pilih Jenis Antrean</div>
                        <div class="text-primary-300 text-xs mt-0.5">Ketuk tombol antrean yang sesuai</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-white/10 text-white font-bold text-sm flex items-center justify-center shrink-0">
                        2</div>
                    <div class="pt-0.5">
                        <div class="font-semibold text-sm">Konfirmasi</div>
                        <div class="text-primary-300 text-xs mt-0.5">Ketuk sekali lagi untuk mengambil nomor</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-white/10 text-white font-bold text-sm flex items-center justify-center shrink-0">
                        3</div>
                    <div class="pt-0.5">
                        <div class="font-semibold text-sm">Cetak & Tunggu</div>
                        <div class="text-primary-300 text-xs mt-0.5">Cetak nomor dan tunggu dipanggil petugas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel Kanan: Tombol Antrean --}}
    <div class="w-3/5 h-full flex flex-col justify-center py-2 print:hidden">
        <div
            class="w-full bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl shadow-black/25 p-6 ring-1 ring-white/50 space-y-4">

            <div class="text-center mb-2">
                <div class="text-rose-800 font-bold text-xl uppercase tracking-widest">Ambil Antrean</div>
                <div class="text-gray-400 text-sm mt-0.5">
                    @if ($queueMode >= 2)
                        Pilih jenis antrean yang sesuai, lalu ketuk sekali lagi untuk konfirmasi
                    @else
                        Ketuk tombol di bawah, lalu ketuk sekali lagi untuk konfirmasi
                    @endif
                </div>

            </div>

            <div class="{{ $queueMode >= 2 ? 'grid grid-cols-2 gap-4' : 'flex flex-col gap-4' }}">

                {{-- Block Regular --}}
                <div class="flex flex-col gap-3 rounded-2xl overflow-hidden ring-2 transition-all duration-300"
                    :class="activeBlock === 'regular' ? 'ring-primary-700' : 'ring-gray-100'">

                    {{-- Nomor Antrean --}}
                    <div
                        class="relative bg-gradient-to-b from-primary-600 to-primary-800 flex flex-col items-center justify-center py-8 overflow-hidden">
                        <div class="absolute -bottom-6 -left-6 w-28 h-28 rounded-full bg-white/5"></div>
                        <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-white/5"></div>
                        <div class="relative z-10 text-center">
                            <div class="text-secondary-300/70 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">
                                Antrean
                            </div>
                            <div
                                class="font-black font-mono text-white leading-none drop-shadow-lg {{ $queueMode >= 2 ? 'text-7xl' : 'text-9xl' }}">
                                {{ $queueNumber }}
                            </div>
                            <div class="mt-3 h-0.5 w-12 bg-secondary-300/40 mx-auto rounded-full"></div>
                            <div class="mt-2 text-secondary-300 font-semibold text-sm">
                                {{ $queueMode === 1 ? 'Antrean Loket' : 'Regular' }}</div>
                        </div>
                    </div>

                    {{-- Info & Tombol --}}
                    <div class="px-4 pb-4 flex flex-col gap-2">
                        <div class="text-gray-400 text-xs text-center">
                            {{ $queueMode === 1 ? 'Semua pasien pendaftaran' : 'Pasien baru & lama' }}</div>

                        <button type="button"
                            x-on:click="if (!handleBlock('regular', '{{ $queueMode === 1 ? 'Antrean loket. Ketuk sekali lagi untuk mengambil nomor.' : 'Regular. Untuk pasien baru dan lama, perubahan data' }}')) return; $wire.takeQueueNumber();"
                            class="w-full py-4 h-16 justify-center rounded-xl font-bold text-lg uppercase tracking-wide text-white transition-all duration-150 cursor-pointer flex flex-col items-center gap-1"
                            :class="activeBlock === 'regular' ? 'bg-primary-700 shadow-lg shadow-primary-500/30' :
                                'bg-primary-700 hover:bg-primary-600'">
                            <span x-show="activeBlock !== 'regular'">Ambil Antrean</span>
                            <span class="text-xs font-normal opacity-90" x-show="activeBlock === 'regular'"
                                x-cloak>Ketuk 1x lagi untuk melanjutkan</span>
                        </button>
                    </div>
                </div>

                {{-- Block Prioritas --}}
                @if ($queueMode >= 2)
                    <div class="flex flex-col gap-3 rounded-2xl overflow-hidden ring-2 transition-all duration-300"
                        :class="activeBlock === 'prioritas' ? 'ring-blue-500' : 'ring-gray-100'">

                        {{-- Nomor Antrean --}}
                        <div
                            class="relative bg-gradient-to-b from-blue-600 to-blue-800 flex flex-col items-center justify-center py-8 overflow-hidden">
                            <div class="absolute -bottom-6 -left-6 w-28 h-28 rounded-full bg-white/5"></div>
                            <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-white/5"></div>
                            <div class="relative z-10 text-center">
                                <div class="text-white/60 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Antrean
                                </div>
                                <div class="font-black font-mono text-white leading-none drop-shadow-lg text-7xl">
                                    {{ $priorityQueueNumber }}
                                </div>
                                <div class="mt-3 h-0.5 w-12 bg-white/30 mx-auto rounded-full"></div>
                                <div class="mt-2 text-white/80 font-semibold text-sm">Prioritas</div>
                            </div>
                        </div>

                        {{-- Info & Tombol --}}
                        <div class="px-4 pb-4 flex flex-col gap-2">
                            <div class="text-gray-400 text-xs text-center">Lansia, disabilitas & ibu hamil</div>

                            <button type="button"
                                x-on:click="if (!handleBlock('prioritas', 'Prioritas. Untuk pasien lansia, disabilitas, dan ibu hamil')) return; $wire.takePriorityQueueNumber();"
                                class="w-full py-4 h-16 justify-center rounded-xl font-bold text-lg uppercase tracking-wide text-white transition-all duration-150 cursor-pointer flex flex-col items-center gap-1"
                                :class="activeBlock === 'prioritas' ? 'bg-blue-600 shadow-lg shadow-blue-500/30' :
                                    'bg-blue-500 hover:bg-blue-600'">
                                <span x-show="activeBlock !== 'prioritas'">Ambil Antrean</span>
                                <span class="text-xs font-normal opacity-90" x-show="activeBlock === 'prioritas'"
                                    x-cloak>Ketuk 1x lagi untuk melanjutkan</span>
                            </button>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

</div>
