<div class="h-full flex items-center gap-6">

    {{-- Panel Kiri: Panduan --}}
    <div class="w-2/5 flex flex-col justify-center py-2">
        <div
            class="h-full flex flex-col justify-center gap-6 backdrop-blur-md bg-primary-900/75 rounded-2xl px-8 py-8 text-white ring-1 ring-white/10 shadow-xl">

            {{-- Brand accent --}}
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-2xl bg-secondary-300/15 flex items-center justify-center ring-2 ring-secondary-300/30 shrink-0">
                    <svg class="w-8 h-8 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
                <div>
                    <div class="text-secondary-300 font-bold text-2xl uppercase tracking-widest leading-tight">Cek Peserta
                    </div>
                    <div class="text-primary-300 text-sm">Status Keaktifan BPJS</div>
                </div>
            </div>

            <div class="border-t border-white/10"></div>

            {{-- Metode pencarian --}}
            <div class="space-y-4">
                <div class="text-primary-300 text-xs font-semibold uppercase tracking-widest">Metode Pencarian</div>

                <div class="space-y-3">
                    <div class="flex items-center gap-3 rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                        <div
                            class="w-12 h-8 rounded-lg bg-secondary-300/20 flex items-center justify-center shrink-0 text-secondary-300 font-bold text-xs">
                            6
                        </div>
                        <div>
                            <div class="font-semibold text-sm">Nomor Rekam Medis</div>
                            <div class="text-primary-300 text-xs mt-0.5">6 digit — tertera pada kartu berobat</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                        <div
                            class="w-12 h-8 rounded-lg bg-secondary-300/20 flex items-center justify-center shrink-0 text-secondary-300 font-bold text-xs">
                            13
                        </div>
                        <div>
                            <div class="font-semibold text-sm">Nomor Kartu BPJS</div>
                            <div class="text-primary-300 text-xs mt-0.5">13 digit — tertera pada kartu BPJS</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                        <div
                            class="w-12 h-8 rounded-lg bg-secondary-300/20 flex items-center justify-center shrink-0 text-secondary-300 font-bold text-xs">
                            16
                        </div>
                        <div>
                            <div class="font-semibold text-sm">NIK / KTP</div>
                            <div class="text-primary-300 text-xs mt-0.5">16 digit — Nomor Induk Kependudukan</div>
                        </div>
                    </div>
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
                        <div class="font-semibold text-sm">Masukkan Nomor Identitas</div>
                        <div class="text-primary-300 text-xs mt-0.5">No. RM, No. BPJS, atau NIK</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-white/10 text-white font-bold text-sm flex items-center justify-center shrink-0">
                        2</div>
                    <div class="pt-0.5">
                        <div class="font-semibold text-sm">Lihat Status Keaktifan</div>
                        <div class="text-primary-300 text-xs mt-0.5">Status aktif / non-aktif peserta BPJS</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-white/10 text-white font-bold text-sm flex items-center justify-center shrink-0">
                        3</div>
                    <div class="pt-0.5">
                        <div class="font-semibold text-sm">Informasi Peserta</div>
                        <div class="text-primary-300 text-xs mt-0.5">Detail data kepesertaan BPJS Kesehatan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel Kanan: Numpad --}}
    <div class="w-3/5 h-full flex items-center justify-center py-2">
        <div
            class="w-full bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl shadow-black/25 p-6 ring-1 ring-white/50">

            <div class="text-center mb-5">
                <div class="text-primary-800 font-bold text-xl uppercase tracking-widest">Cari Peserta</div>
                <div class="text-gray-400 text-sm mt-0.5">Masukkan nomor identitas atau scan barcode kartu BPJS</div>
            </div>

            <livewire:numpad.numpad-basic placeholder="No. RM/No. BPJS/NIK" maxLength="16" :value="$idNumber"
                updatedTrigger="numpad-updated" enteredTrigger="numpad-entered" :autoDetect="false" />
        </div>
    </div>

</div>
