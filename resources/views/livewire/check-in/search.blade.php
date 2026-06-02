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
                            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                </div>
                <div>
                    <div class="text-secondary-300 font-bold text-2xl uppercase tracking-widest leading-tight">Check In
                        Mandiri</div>
                    <div class="text-primary-300 text-sm">BPJS Kesehatan / Mobile JKN</div>
                </div>
            </div>

            <div class="border-t border-white/10"></div>

            {{-- Langkah-langkah --}}
            <div class="space-y-5">
                <div class="text-primary-300 text-xs font-semibold uppercase tracking-widest">Cara Penggunaan</div>

                <div class="flex items-start gap-4">
                    <div
                        class="w-9 h-9 rounded-full bg-secondary-300 text-primary-900 font-bold text-base flex items-center justify-center shrink-0 shadow-lg shadow-secondary-300/20">
                        1</div>
                    <div class="pt-0.5">
                        <div class="font-semibold text-base">Masukkan Kode Booking</div>
                        <div class="text-primary-300 text-sm mt-0.5 leading-relaxed">Kode booking 15 digit dari aplikasi
                            Mobile JKN atau scan barcode</div>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div
                        class="w-9 h-9 rounded-full bg-white/10 text-white font-bold text-base flex items-center justify-center shrink-0">
                        2</div>
                    <div class="pt-0.5">
                        <div class="font-semibold text-base">Verifikasi Data Peserta</div>
                        <div class="text-primary-300 text-sm mt-0.5 leading-relaxed">Konfirmasi identitas dan
                            kelayakan peserta BPJS Anda</div>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div
                        class="w-9 h-9 rounded-full bg-white/10 text-white font-bold text-base flex items-center justify-center shrink-0">
                        3</div>
                    <div class="pt-0.5">
                        <div class="font-semibold text-base">Check In Selesai</div>
                        <div class="text-primary-300 text-sm mt-0.5 leading-relaxed">Silahkan menuju poli dan tunggu
                            dipanggil oleh petugas</div>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/10"></div>

            {{-- Info box --}}
            <div class="rounded-xl bg-secondary-300/10 ring-1 ring-secondary-300/20 p-4 space-y-1.5">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-secondary-300 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <div class="text-secondary-300 text-xs font-semibold uppercase tracking-wider">Perhatian</div>
                </div>
                <div class="text-primary-200 text-sm leading-relaxed">Check In dibuka <span
                        class="text-white font-semibold">30 menit sebelum</span> jam praktek hingga jam selesai.
                    Pastikan kode booking sesuai jadwal hari ini.</div>
            </div>
        </div>
    </div>

    {{-- Panel Kanan: Numpad --}}
    <div class="w-3/5 h-full flex items-center justify-center py-2">
        <div
            class="w-full bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl shadow-black/25 p-6 ring-1 ring-white/50">

            <div class="text-center mb-5">
                <div class="text-primary-800 font-bold text-xl uppercase tracking-widest">Kode Booking</div>
                <div class="text-gray-400 text-sm mt-0.5">Ketuk angka atau scan barcode tiket Mobile JKN</div>
            </div>

            <livewire:numpad.numpad-care-number placeholder="Kode Booking" maxLength="15" wire:model="idNumber"
                updatedTrigger="numpad-updated" enteredTrigger="numpad-entered" :autoDetect="false" careCode="B">
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <flux:modal.trigger name="checkViaPoli">
                        <button type="button"
                            @click="$dispatch('resetSchedules'); $dispatch('speak', { text: 'Via Antrean Mobile JKN' });"
                            class="w-full py-3 px-4 rounded-xl border-2 border-primary-700 bg-primary-700 text-secondary-300 font-semibold text-base uppercase tracking-wide cursor-pointer flex items-center justify-center gap-2.5 transition-all duration-150 hover:bg-primary-700 hover:text-secondary-300 active:bg-primary-800 active:text-secondary-300">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Via Antrean Mobile JKN
                        </button>
                    </flux:modal.trigger>
                </div>
            </livewire:numpad.numpad-care-number>
        </div>
    </div>

    {{-- Modal JKN Order via Poli --}}
    <flux:modal name="checkViaPoli" class="w-full max-w-5xl" :dismissible="false" :closable="false">
        <livewire:check-in.jkn-order careCode="B" />
        <div class="mt-4 flex justify-end">
            <flux:button variant="primary" color="gray" x-on:click="$flux.modal('checkViaPoli').close()">
                Kembali
            </flux:button>
        </div>
    </flux:modal>

    {{-- Modal Processing BPJS Check --}}
    <flux:modal name="processCheckin" class="w-full max-w-md" :dismissible="false" :closable="false">
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
                <div wire:init="processBpjsCheck" class="mt-4">
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

            <livewire:patient-info :isNewPatientButton="false" :$patient wire:key="patientData" :$isLoading lazy />
        </div>
    </flux:modal>


</div>
