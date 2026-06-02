<div class="space-y-4">

    <div class="flex flex-row gap-4">

        {{-- Data Pasien --}}
        <div class="flex-1 rounded-2xl overflow-hidden shadow-xl bg-white/90 backdrop-blur-xl">
            <div class="px-5 py-3 bg-primary-700 flex items-center gap-2">
                <flux:icon.user class="size-5 text-secondary-300" />
                <h3 class="font-black text-white text-base uppercase tracking-widest">Data Pasien</h3>
            </div>
            <div class="p-5 grid grid-cols-2 gap-x-5 gap-y-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">No. Rekam Medis</p>
                    <p class="text-lg font-black text-gray-800">{{ $patient->no_rkm_medis }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Nama Pasien</p>
                    <p class="text-lg font-black text-gray-800 leading-tight">{{ $patient->nm_pasien }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Tanggal Lahir</p>
                    <p class="text-lg font-black text-gray-800">
                        {{ \Carbon\Carbon::parse($patient->tgl_lahir)->format('d-m-Y') }}
                        <span
                            class="text-sm font-normal text-gray-500">({{ \Carbon\Carbon::parse($patient->tgl_lahir)->age }}
                            thn)</span>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Jenis Kelamin</p>
                    <p class="text-lg font-black text-gray-800">{{ $patient->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Alamat</p>
                    <p class="text-base font-bold text-gray-800 leading-snug">{{ $patient->alamat ?? '-' }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Jenis Bayar</p>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-100 text-primary-800 text-sm font-bold border border-primary-200">
                        <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                        {{ $payType['png_jawab'] ?? '-' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Data Jadwal --}}
        @if (!empty($schedule))
            <div class="flex-1 rounded-2xl overflow-hidden shadow-xl bg-white/90 backdrop-blur-xl">
                <div class="px-5 py-3 bg-primary-700 flex items-center gap-2">
                    <flux:icon.calendar-days class="size-5 text-secondary-300" />
                    <h3 class="font-black text-white text-base uppercase tracking-widest">Jadwal Pendaftaran</h3>
                </div>
                <div class="p-5 grid grid-cols-2 gap-x-5 gap-y-4">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Poliklinik</p>
                        <p class="text-lg font-black text-gray-800 leading-tight">
                            {{ $schedule['nm_poli'] ?? ($schedule['polyclinic']['nm_poli'] ?? '-') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Dokter</p>
                        <p class="text-base font-bold text-gray-800 leading-snug">
                            {{ $schedule['nm_dokter'] ?? ($schedule['doctor']['nm_dokter'] ?? '-') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Hari</p>
                        <p class="text-lg font-black text-gray-800">{{ $schedule['hari_kerja'] ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Jam Praktik</p>
                        <p class="text-lg font-black text-gray-800">
                            {{ \Carbon\Carbon::parse($schedule['jam_mulai'])->format('H:i') }} –
                            {{ \Carbon\Carbon::parse($schedule['jam_selesai'])->format('H:i') }}
                        </p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Tanggal Pendaftaran</p>
                        <p class="text-lg font-black text-gray-800">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </p>
                    </div>
                </div>

                {{-- Peringatan --}}
                <div class="mx-5 mb-5 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-2">
                    <flux:icon.exclamation-triangle class="size-4 text-amber-500 shrink-0 mt-0.5" />
                    <p class="text-xs text-amber-700 leading-snug">
                        Pastikan data di atas sudah benar sebelum melanjutkan pendaftaran.
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- Tombol Konfirmasi --}}
    <button wire:click="register" wire:loading.attr="disabled"
        class="w-full h-20 rounded-2xl bg-gradient-to-br from-primary-700 to-primary-500 hover:to-yellow-300 active:scale-[0.99] transition-all duration-200 shadow-xl flex items-center justify-center gap-3 disabled:opacity-60 disabled:cursor-not-allowed">
        <svg wire:loading.remove wire:target="register" class="size-8 text-secondary-300" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <svg wire:loading wire:target="register" class="size-8 text-white animate-spin" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <span wire:loading.remove wire:target="register"
            class="text-3xl font-black text-secondary-300 uppercase tracking-widest drop-shadow">
            Konfirmasi &amp; Daftar
        </span>
        <span wire:loading wire:target="register" class="text-3xl font-black text-white uppercase tracking-widest">
            Memproses...
        </span>
    </button>

</div>
