<div class="flex gap-4 h-[62vh]" x-data="{
    polyclinics: @js($polyclinics),
    queues: [],
    selectedPoli: null,
    activeQueue: null,
    loadingQueues: false,

    async selectPoli(poli) {
        if (this.selectedPoli?.key === poli.key) return;
        this.selectedPoli = poli;
        this.activeQueue = null;
        this.queues = [];
        this.loadingQueues = true;
        $dispatch('speak', { text: poli.nm_poli + '. ' + poli.nm_dokter });
        try {
            this.queues = await $wire.getQueues(poli.kd_poli, poli.kd_dokter);
        } finally {
            this.loadingQueues = false;
        }
    },

    handleQueue(noRawat, speakText) {
        if (this.activeQueue === noRawat) {
            $wire.selectQueue(noRawat);
            this.activeQueue = null;
        } else {
            this.activeQueue = noRawat;
            $dispatch('speak', { text: speakText });
        }
    }
}"
    @schedules-reset.window="polyclinics = $event.detail.polyclinics; selectedPoli = null; queues = []; activeQueue = null;">

    {{-- Kiri: Daftar Poliklinik --}}
    <div class="w-2/5 flex flex-col gap-3">
        <div
            class="bg-primary-700 text-lg text-secondary-300 text-center py-2 px-4 font-bold uppercase tracking-widest rounded-lg shrink-0">
            Pilih Poliklinik
        </div>

        <template x-if="polyclinics.length === 0">
            <div class="flex-1 flex items-center justify-center text-gray-400 text-lg text-center px-4">
                Tidak ada jadwal praktek hari ini
            </div>
        </template>

        <template x-if="polyclinics.length > 0">
            <div class="overflow-y-auto flex-1 space-y-2 pr-1">
                <template x-for="poli in polyclinics" :key="poli.key">
                    <button @click="poli.is_active && selectPoli(poli)" :disabled="!poli.is_active || loadingQueues"
                        :class="poli.is_active ?
                            (selectedPoli?.key === poli.key ?
                                'bg-primary-800 border-primary-800 cursor-default' :
                                'bg-white border-gray-300 hover:bg-primary-700 hover:border-primary-700 active:bg-primary-800 cursor-pointer'
                            ) :
                            'bg-red-50 border-red-200 opacity-70 cursor-not-allowed'"
                        class="w-full text-left px-4 py-3 rounded-lg border transition-all duration-150 group">
                        {{-- Nama Poliklinik --}}
                        <div :class="poli.is_active ?
                            (selectedPoli?.key === poli.key ? 'text-secondary-300' :
                                'text-gray-800 group-hover:text-secondary-300') :
                            'text-red-700'"
                            class="font-semibold text-base leading-tight" x-text="poli.nm_poli">
                        </div>
                        {{-- Nama Dokter + Jam --}}
                        <div class="flex items-center justify-between gap-2 mt-0.5">
                            <span
                                :class="poli.is_active ?
                                    (selectedPoli?.key === poli.key ? 'text-white' :
                                        'text-gray-600 group-hover:text-white') :
                                    'text-red-400'"
                                class="text-sm truncate" x-text="poli.nm_dokter">
                            </span>
                            <span
                                :class="poli.is_active ?
                                    (selectedPoli?.key === poli.key ? 'text-secondary-300/80' :
                                        'text-gray-400 group-hover:text-white/70') :
                                    'text-red-300'"
                                class="text-xs shrink-0 tabular-nums"
                                x-text="poli.jam_mulai + ' \u2013 ' + poli.jam_selesai">
                            </span>
                        </div>
                        {{-- Status --}}
                        <template x-if="!poli.is_active">
                            <div class="text-xs text-red-500 mt-1"
                                x-text="'Tutup \u00b7 Check in tersedia pada ' + poli.jam_check_in + ' \u2013 ' + poli.jam_selesai">
                            </div>
                        </template>
                    </button>
                </template>
            </div>
        </template>
    </div>

    {{-- Kanan: Daftar Nomor Antrean --}}
    <div class="w-3/5 flex flex-col gap-3 relative">
        <div class="bg-primary-700 text-secondary-300 text-center py-2 px-4 rounded-lg shrink-0">
            <template x-if="!selectedPoli">
                <span class="text-lg font-bold uppercase tracking-widest">Pilih Nomor Antrean</span>
            </template>
            <template x-if="selectedPoli">
                <div>
                    <div class="text-lg font-bold uppercase tracking-widest" x-text="selectedPoli.nm_poli"></div>
                    <div class="text-sm font-normal text-white/80 mt-0.5" x-text="selectedPoli.nm_dokter"></div>
                </div>
            </template>
        </div>

        {{-- Loading overlay --}}
        <div x-show="loadingQueues" x-cloak
            class="absolute inset-0 top-0 z-10 bg-white/80 rounded-lg flex items-center justify-center gap-3">
            <flux:icon.loading class="size-6 text-primary-700" />
            <div class="text-primary-700 font-semibold text-lg">Memuat antrean...</div>
        </div>

        {{-- Belum pilih poli --}}
        <template x-if="!selectedPoli">
            <div class="flex-1 flex flex-col items-center justify-center text-gray-400 text-lg text-center px-8 gap-3">
                <svg class="size-12 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Pilih poliklinik terlebih dahulu
            </div>
        </template>

        {{-- Antrean kosong --}}
        <template x-if="selectedPoli && !loadingQueues && queues.length === 0">
            <div class="flex-1 flex flex-col items-center justify-center text-gray-400 text-lg text-center px-8 gap-3">
                <svg class="size-12 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
                Tidak ada antrean tersedia untuk poliklinik ini
            </div>
        </template>

        {{-- Daftar antrean --}}
        <template x-if="selectedPoli && !loadingQueues && queues.length > 0">
            <div class="overflow-y-auto flex-1 space-y-2 pr-1">
                <template x-for="queue in queues" :key="queue.no_rawat">
                    <button
                        @click="handleQueue(queue.no_rawat, queue.kd_poli + '-' + queue.no_reg + '. ' + queue.nm_pasien.toLowerCase())"
                        :class="activeQueue === queue.no_rawat ?
                            'border-primary-700 bg-primary-700' :
                            'bg-white border-gray-300 hover:bg-primary-700 hover:border-primary-700 active:bg-primary-800'"
                        class="w-full cursor-pointer text-left px-4 py-3 rounded-lg border transition-all duration-150 relative overflow-hidden group">
                        <div class="flex items-center gap-2 mb-1">
                            <span
                                :class="activeQueue === queue.no_rawat ? 'bg-secondary-300 text-primary-900' :
                                    'bg-primary-100 text-primary-700 group-hover:bg-primary-600 group-hover:text-white'"
                                class="shrink-0 text-xs font-bold px-2 py-0.5 rounded uppercase tracking-wide"
                                x-text="queue.kd_poli + '-' + queue.no_reg">
                            </span>
                            <span
                                :class="activeQueue === queue.no_rawat ? 'text-secondary-300' :
                                    'text-primary-700 group-hover:text-white'"
                                class="text-lg font-bold" x-text="queue.no_rawat">
                            </span>
                        </div>
                        <div :class="activeQueue === queue.no_rawat ? 'text-white' : 'text-gray-500 group-hover:text-primary-100'"
                            class="text-sm font-normal" x-text="queue.nm_pasien">
                        </div>

                        {{-- Badge pengingat klik kedua --}}
                        <div x-show="activeQueue === queue.no_rawat"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0" class="absolute bottom-1.5 right-2 pointer-events-none">
                            <span
                                class="inline-flex items-center gap-1 bg-secondary-300 text-primary-900 text-xs font-bold px-2.5 py-1 rounded-full animate-bounce shadow">
                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                                Ketuk lagi untuk konfirmasi
                            </span>
                        </div>
                    </button>
                </template>
            </div>
        </template>
    </div>
</div>
