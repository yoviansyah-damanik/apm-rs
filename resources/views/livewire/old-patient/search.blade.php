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
                            d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-secondary-300 font-bold text-2xl uppercase tracking-widest leading-tight">Antrean Poli
                    </div>
                    <div class="text-primary-300 text-sm">Pendaftaran Rawat Jalan</div>
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
                        <div class="font-semibold text-sm">Verifikasi Data</div>
                        <div class="text-primary-300 text-xs mt-0.5">Konfirmasi identitas pasien</div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-white/10 text-white font-bold text-sm flex items-center justify-center shrink-0">
                        3</div>
                    <div class="pt-0.5">
                        <div class="font-semibold text-sm">Pilih Layanan</div>
                        <div class="text-primary-300 text-xs mt-0.5">Pilih poliklinik dan jadwal kunjungan</div>
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
                <div class="text-primary-800 font-bold text-xl uppercase tracking-widest">Cari Data Pasien</div>
                <div class="text-gray-400 text-sm mt-0.5">Masukkan nomor identitas atau scan barcode kartu berobat
                </div>
            </div>

            <livewire:numpad.numpad-basic placeholder="No. RM/No. BPJS/NIK" maxLength="16" :value="$idNumber"
                updatedTrigger="numpad-updated" enteredTrigger="numpad-entered" :autoDetect="false" />
        </div>
    </div>

    {{-- Modal Processing Patient Check --}}
    <flux:modal name="processPatient" class="w-full max-w-md" :dismissible="false" :closable="false">
        <div class="space-y-4 text-center py-8">
            <div class="flex justify-center">
                <flux:icon.arrow-path class="size-16 text-primary-700 animate-spin" />
            </div>
            <div>
                <flux:heading size="lg" class="mb-2">Mencari Data Pasien</flux:heading>
                <flux:text class="text-gray-600">
                    Sedang melakukan pencarian data pasien di sistem.
                    Mohon tunggu sebentar...
                </flux:text>
            </div>

            @if ($showProcessModal)
                <div wire:init="processPatientCheck" class="mt-4">
                    <div class="space-y-2">
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-primary-700 animate-pulse" style="width: 100%"></div>
                        </div>
                        <flux:text class="text-xs text-gray-500">Menghubungkan ke server...</flux:text>
                    </div>
                </div>
            @endif
        </div>
    </flux:modal>

    {{-- Modal Data Peserta --}}
    <flux:modal name="participantData" class="w-full" :dismissible="false" :closable="false">
        <div class="space-y-3">
            <div>
                <flux:heading size="lg">Data Rekam Medis</flux:heading>
                <flux:text>Pastikan data anda benar untuk melanjutkan pendaftaran.</flux:text>
            </div>

            <livewire:patient-info :$patient wire:key="patientData" :$isLoading lazy />
        </div>
    </flux:modal>

</div>
