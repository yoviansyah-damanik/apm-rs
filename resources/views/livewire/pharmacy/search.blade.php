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
                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <div>
                    <div class="text-secondary-300 font-bold text-2xl uppercase tracking-widest leading-tight">Antrean
                        Farmasi</div>
                    <div class="text-primary-300 text-sm">Pengambilan Obat</div>
                </div>
            </div>

            <div class="border-t border-white/10"></div>

            {{-- Format kode --}}
            <div class="space-y-4">
                <div class="text-primary-300 text-xs font-semibold uppercase tracking-widest">Format Kode Booking</div>

                <div class="space-y-2.5">
                    <div class="flex items-center gap-3 rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                        <div
                            class="w-10 h-8 rounded-lg bg-secondary-300/20 flex items-center justify-center shrink-0 text-secondary-300 font-bold text-sm">
                            A
                        </div>
                        <div>
                            <div class="font-semibold text-sm">Umum / BPJS Onsite</div>
                            <div class="text-primary-300 text-xs mt-0.5">Kode booking diawali huruf A</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                        <div
                            class="w-10 h-8 rounded-lg bg-secondary-300/20 flex items-center justify-center shrink-0 text-secondary-300 font-bold text-sm">
                            B
                        </div>
                        <div>
                            <div class="font-semibold text-sm">BPJS / Mobile JKN</div>
                            <div class="text-primary-300 text-xs mt-0.5">Kode booking diawali huruf B</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                        <div
                            class="w-10 h-8 rounded-lg bg-secondary-300/20 flex items-center justify-center shrink-0 text-secondary-300 font-bold text-sm">
                            D
                        </div>
                        <div>
                            <div class="font-semibold text-sm">APM</div>
                            <div class="text-primary-300 text-xs mt-0.5">Kode booking diawali huruf D</div>
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
                        <div class="font-semibold text-sm">Masukkan Kode Booking</div>
                        <div class="text-primary-300 text-xs mt-0.5">15 karakter dari struk / nota poliklinik</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-white/10 text-white font-bold text-sm flex items-center justify-center shrink-0">
                        2</div>
                    <div class="pt-0.5">
                        <div class="font-semibold text-sm">Lihat Nomor Antrean</div>
                        <div class="text-primary-300 text-xs mt-0.5">Cek nomor antrean farmasi</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-white/10 text-white font-bold text-sm flex items-center justify-center shrink-0">
                        3</div>
                    <div class="pt-0.5">
                        <div class="font-semibold text-sm">Cetak</div>
                        <div class="text-primary-300 text-xs mt-0.5">Cetak dan tunggu nomor antrean dipanggil</div>
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
                <div class="text-primary-800 font-bold text-xl uppercase tracking-widest">Kode Booking</div>
                <div class="text-gray-400 text-sm mt-0.5">Masukkan kode booking dari struk poliklinik</div>
            </div>

            <livewire:numpad.numpad-alphanumeric placeholder="Kode Booking" maxLength="15" :value="$idNumber"
                updatedTrigger="numpad-updated" enteredTrigger="numpad-entered" :autoDetect="false" />
        </div>
    </div>

</div>
