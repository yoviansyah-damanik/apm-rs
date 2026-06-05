<div class="flex flex-col gap-4 pb-6">

    {{-- Header --}}
    <div class="px-1 pt-2 text-center">
        <p class="text-base font-bold uppercase tracking-[0.2em] text-secondary-300 mb-0.5">Sistem</p>
        <h1 class="text-4xl font-black text-white drop-shadow-lg uppercase">Uji Fungsi</h1>
    </div>

    {{-- Card: Informasi Sistem --}}
    <div class="rounded-2xl shadow-xl bg-white/90 backdrop-blur-xl overflow-hidden">
        <div class="px-6 py-4 bg-primary-700">
            <p class="font-black text-white text-lg leading-tight">Informasi Sistem</p>
            <p class="text-sm text-white/70">Versi runtime yang sedang berjalan</p>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach ([
                    ['label' => 'PHP',          'value' => PHP_VERSION,          'icon' => 'code-bracket'],
                    ['label' => 'Laravel',      'value' => app()->version(),      'icon' => 'cube'],
                    ['label' => 'DB APM',       'value' => $dbApmVersion,        'icon' => 'circle-stack'],
                    ['label' => 'DB SIMRS',     'value' => $dbSimrsVersion,      'icon' => 'server'],
                    ['label' => 'OS',           'value' => PHP_OS,               'icon' => 'computer-desktop'],
                    ['label' => 'Mode',         'value' => config('app.env'),    'icon' => 'cog-6-tooth'],
                ] as $info)
                    <div class="flex items-center gap-3 rounded-2xl border-2 border-gray-200 bg-white p-4">
                        <div class="shrink-0 w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center">
                            <flux:icon :name="$info['icon']" class="size-5 text-primary-500" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide">{{ $info['label'] }}</p>
                            <p class="text-sm font-black text-gray-800 truncate">{{ $info['value'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
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
                    ['key' => 'db_simrs',    'label' => 'Database SIMRS', 'desc' => 'Database SIMRS (simrs)',                 'icon' => 'server',       'method' => 'testDbSimrs'],
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

    {{-- Card: Uji API BPJS --}}
    <div class="rounded-2xl shadow-xl bg-white/90 backdrop-blur-xl overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 bg-primary-700">
            <div>
                <p class="font-black text-white text-lg leading-tight">Uji API BPJS</p>
                <p class="text-sm text-white/70">Masukkan nomor kartu / NIK untuk uji endpoint BPJS</p>
            </div>
            <button wire:click="runAllBpjsTests" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-secondary-300 text-primary-900 font-bold text-sm hover:bg-yellow-300 active:scale-95 transition-all disabled:opacity-60 disabled:cursor-not-allowed shadow">
                <svg wire:loading.remove wire:target="runAllBpjsTests" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg wire:loading wire:target="runAllBpjsTests" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <span wire:loading.remove wire:target="runAllBpjsTests">Uji Semua</span>
                <span wire:loading wire:target="runAllBpjsTests">Menguji...</span>
            </button>
        </div>

        <div class="p-5 flex flex-col gap-4">

            {{-- Input nomor kartu / NIK --}}
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <flux:icon.identification class="absolute left-3 top-1/2 -translate-y-1/2 size-5 text-gray-400 pointer-events-none" />
                    <input
                        wire:model="bpjsParticipantNumber"
                        type="text"
                        placeholder="Nomor Kartu BPJS (13 digit) atau NIK (16 digit)"
                        maxlength="16"
                        class="w-full pl-10 pr-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary-400 focus:ring-0 focus:outline-none text-sm font-medium text-gray-800 placeholder:text-gray-300 transition-colors"
                    />
                </div>
                <button wire:click="$set('bpjsParticipantNumber', '')"
                    class="p-3 rounded-xl border-2 border-gray-200 hover:border-red-300 hover:bg-red-50 text-gray-400 hover:text-red-500 transition-all">
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>

            {{-- Kartu uji BPJS --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach ([
                    ['key' => 'participant',    'label' => 'Cek Peserta',      'desc' => 'GET /Peserta — validasi data kepesertaan BPJS',     'icon' => 'user-circle',  'method' => 'testBpjsParticipant'],
                    ['key' => 'references',     'label' => 'Cek Rujukan',      'desc' => 'GET /Rujukan/List — daftar rujukan aktif peserta',   'icon' => 'document-text','method' => 'testBpjsReferences'],
                    ['key' => 'control_letters','label' => 'Cek Surat Kontrol','desc' => 'GET /KontrolGeografis — surat kontrol peserta',      'icon' => 'clipboard-document-check', 'method' => 'testBpjsControlLetters'],
                ] as $item)
                    @include('livewire.function-test-card', [...$item, 'resultsData' => $bpjsResults])
                @endforeach
            </div>

            @if (!empty($bpjsResults))
                @php
                    $bpjsSuccess = collect($bpjsResults)->where('status', 'success')->count();
                    $bpjsError   = collect($bpjsResults)->where('status', 'error')->count();
                @endphp
                <div class="flex flex-wrap items-center gap-3 px-5 py-3 rounded-2xl bg-primary-50 border border-primary-200">
                    <flux:icon.chart-bar class="size-5 text-primary-600 shrink-0" />
                    <p class="text-sm font-semibold text-primary-800">Ringkasan:</p>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-600 text-white text-sm font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-secondary-300"></span>
                        {{ $bpjsSuccess }} Berhasil
                    </span>
                    @if ($bpjsError > 0)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500 text-white text-sm font-bold">
                            {{ $bpjsError }} Gagal
                        </span>
                    @endif
                    <span class="text-sm text-gray-400">dari {{ count($bpjsResults) }} diuji</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Card: Uji Fungsi --}}
    <div class="rounded-2xl shadow-xl bg-white/90 backdrop-blur-xl overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 bg-primary-700">
            <div>
                <p class="font-black text-white text-lg leading-tight">Uji Fungsi</p>
                <p class="text-sm text-white/70">Klik kartu untuk menjalankan fungsi</p>
            </div>
            <button wire:click="runAllFunctions" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-secondary-300 text-primary-900 font-bold text-sm hover:bg-yellow-300 active:scale-95 transition-all disabled:opacity-60 disabled:cursor-not-allowed shadow">
                <svg wire:loading.remove wire:target="runAllFunctions" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg wire:loading wire:target="runAllFunctions" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <span wire:loading.remove wire:target="runAllFunctions">Jalankan Semua</span>
                <span wire:loading wire:target="runAllFunctions">Menguji...</span>
            </button>
        </div>

        <div class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach ([
                    ['key' => 'queue_status', 'label' => 'Status Antrean',  'desc' => 'Baca nomor antrean berikutnya & sisa dari database',  'icon' => 'queue-list',   'method' => 'testQueueStatus'],
                    ['key' => 'tts',          'label' => 'TTS',             'desc' => 'Putar suara via Web Speech API (Text to Speech)',      'icon' => 'speaker-wave', 'method' => '$js.ttsTrigger()'],
                    ['key' => 'fingerprint',  'label' => 'Fingerprint',     'desc' => 'Kirim trigger BPJS_FINGER_TRIGGER ke NativePHP',       'icon' => 'finger-print', 'method' => '$js.biometricBpjsTrigger(`fingerprint`)'],
                    ['key' => 'frista',       'label' => 'FRISTA',          'desc' => 'Kirim trigger BPJS_FRISTA_TRIGGER ke NativePHP',       'icon' => 'face-smile',   'method' => '$js.biometricBpjsTrigger(`frista`)'],
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
