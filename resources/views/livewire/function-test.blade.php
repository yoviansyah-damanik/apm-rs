<div class="flex flex-col gap-4 pb-6">

    {{-- Header --}}
    <div class="px-1 pt-2 text-center">
        <p class="text-base font-bold uppercase tracking-[0.2em] text-secondary-300 mb-0.5">Sistem</p>
        <h1 class="text-4xl font-black text-white drop-shadow-lg uppercase">Uji Fungsi</h1>
    </div>

    {{-- Card: Uji Koneksi --}}
    <div class="rounded-2xl shadow-xl bg-white/90 backdrop-blur-xl overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 bg-primary-700">
            <div>
                <p class="font-black text-white text-lg leading-tight">Uji Koneksi</p>
                <p class="text-sm text-white/70">Klik kartu untuk uji individual</p>
            </div>
            <button wire:click="runAllConnections" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-secondary-300 text-primary-900 font-bold text-sm hover:bg-yellow-300 active:scale-95 transition-all disabled:opacity-60 disabled:cursor-not-allowed shadow">
                <svg wire:loading.remove wire:target="runAllConnections" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg wire:loading wire:target="runAllConnections" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <span wire:loading.remove wire:target="runAllConnections">Jalankan Semua</span>
                <span wire:loading wire:target="runAllConnections">Menguji...</span>
            </button>
        </div>

        <div class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach ([
                    ['key' => 'db_main',     'label' => 'Database APM',   'desc' => 'Database utama aplikasi (mariadb)',      'icon' => 'circle-stack', 'method' => 'testDbMain'],
                    ['key' => 'db_simrs',    'label' => 'Database SIMRS', 'desc' => 'Database SIMRS (rstni_db_dev)',          'icon' => 'server',       'method' => 'testDbSimrs'],
                    ['key' => 'bpjs_vclaim', 'label' => 'BPJS Vclaim',   'desc' => 'API Vclaim BPJS Kesehatan',             'icon' => 'shield-check', 'method' => 'testBpjsVclaim'],
                    ['key' => 'bpjs_antrol', 'label' => 'BPJS Antrol',   'desc' => 'API Antrian Online BPJS Kesehatan',     'icon' => 'queue-list',   'method' => 'testBpjsAntrol'],
                ] as $item)
                    @include('livewire.function-test-card', [...$item, 'resultsData' => $connectionResults])
                @endforeach
            </div>

            @if (!empty($connectionResults))
                @php
                    $connSuccess = collect($connectionResults)->where('status', 'success')->count();
                    $connError   = collect($connectionResults)->where('status', 'error')->count();
                @endphp
                <div class="flex flex-wrap items-center gap-3 px-5 py-3 rounded-2xl bg-primary-50 border border-primary-200 mt-4">
                    <flux:icon.chart-bar class="size-5 text-primary-600 shrink-0" />
                    <p class="text-sm font-semibold text-primary-800">Ringkasan:</p>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-600 text-white text-sm font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-secondary-300"></span>
                        {{ $connSuccess }} Berhasil
                    </span>
                    @if ($connError > 0)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500 text-white text-sm font-bold">
                            {{ $connError }} Gagal
                        </span>
                    @endif
                    <span class="text-sm text-gray-400">dari {{ count($connectionResults) }} diuji</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Card: Uji Fungsi --}}
    <div class="rounded-2xl shadow-xl bg-white/90 backdrop-blur-xl overflow-hidden">

        <div class="px-6 py-4 bg-primary-700">
            <p class="font-black text-white text-lg leading-tight">Uji Fungsi</p>
            <p class="text-sm text-white/70">Klik kartu untuk menjalankan fungsi</p>
        </div>

        <div class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach ([
                    ['key' => 'frista',      'label' => 'FRISTA',      'desc' => 'Kirim trigger BPJS_FRISTA_TRIGGER ke NativePHP',   'icon' => 'face-smile',   'method' => '$js.biometricBpjsTrigger(`frista`)'],
                    ['key' => 'fingerprint', 'label' => 'Fingerprint', 'desc' => 'Kirim trigger BPJS_FINGER_TRIGGER ke NativePHP',   'icon' => 'finger-print', 'method' => '$js.biometricBpjsTrigger(`fingerprint`)'],
                    ['key' => 'tts',         'label' => 'TTS',         'desc' => 'Putar suara via Web Speech API (Text to Speech)',  'icon' => 'speaker-wave', 'method' => '$js.ttsTrigger()'],
                ] as $item)
                    @include('livewire.function-test-card', [...$item, 'resultsData' => $functionResults])
                @endforeach
            </div>

            @if (!empty($functionResults))
                @php
                    $funcSuccess = collect($functionResults)->where('status', 'success')->count();
                    $funcError   = collect($functionResults)->where('status', 'error')->count();
                @endphp
                <div class="flex flex-wrap items-center gap-3 px-5 py-3 rounded-2xl bg-primary-50 border border-primary-200 mt-4">
                    <flux:icon.chart-bar class="size-5 text-primary-600 shrink-0" />
                    <p class="text-sm font-semibold text-primary-800">Ringkasan:</p>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-600 text-white text-sm font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-secondary-300"></span>
                        {{ $funcSuccess }} Berhasil
                    </span>
                    @if ($funcError > 0)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500 text-white text-sm font-bold">
                            {{ $funcError }} Gagal
                        </span>
                    @endif
                    <span class="text-sm text-gray-400">dari {{ count($functionResults) }} diuji</span>
                </div>
            @endif
        </div>
    </div>

</div>

@script
    <script>
        $js('biometricBpjsTrigger', (mode) => {
            console.log('[INFO]', 'Biometric BPJS Kesehatan Trigger. Mode: ' + mode);
            window.postMessage({
                type: mode == 'fingerprint' ? 'BPJS_FINGER_TRIGGER' : 'BPJS_FRISTA_TRIGGER',
                mode: mode,
                noKartu: '1234567890',
                username: '{{ env('BIOMETRIC_USERNAME') }}',
                password: '{{ env('BIOMETRIC_PASSWORD') }}'
            }, '*');
            $wire.markBiometricTriggered(mode);
        });

        $js('ttsTrigger', () => {
            $dispatch('speak', { text: 'Uji Text to Speech berhasil dijalankan.' });
            $wire.markTtsTriggered();
        });
    </script>
@endscript
