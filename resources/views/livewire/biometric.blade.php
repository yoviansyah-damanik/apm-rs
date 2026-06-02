<div class="space-y-4">
    {{-- Status Validasi Biometrik --}}
    <flux:callout :icon="$biometricStatus['kode'] == 1 ? 'check-circle' : 'x-circle'"
        :color="$biometricStatus['kode'] == 1 ? 'green' : 'rose'" heading="Status Validasi Biometrik" inline>
        <flux:callout.text>
            <div class="space-y-2">
                <p>{{ $biometricStatus['status'] ?? $biometricStatus['message'] }}</p>
                {{-- @if (isset($biometricStatus['biometric_required']))
                    <p class="text-sm {{ $biometricStatus['biometric_required'] ? 'text-rose-700' : 'text-primary-700' }}">
                        Biometrik {{ $biometricStatus['biometric_required'] ? 'Diperlukan' : 'Tidak Diperlukan' }}
                    </p>
                @endif --}}
            </div>
        </flux:callout.text>
        <x-slot name="actions">
            <flux:button variant="primary" :color="$biometricStatus['kode'] == 1 ? 'green' : 'rose'"
                wire:click="checkBiometricStatus" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="checkBiometricStatus">Refresh</span>
                <span wire:loading wire:target="checkBiometricStatus">
                    <flux:icon.loading class="size-5" />
                </span>
            </flux:button>
        </x-slot>
    </flux:callout>

    {{-- Tombol Biometrik - Hanya tampil jika validasi belum sukses --}}
    @if (isset($biometricStatus['kode']) && $biometricStatus['kode'] != 1)
        <div class="grid grid-cols-2 gap-4">
            <flux:button wire:click="$js.biometricBpjsTrigger('fingerprint')"
                wire:target="checkBiometricStatus,launchFingerprint" variant="primary" color="blue" size="2xl"
                class="h-28 !text-5xl tracking-widest uppercase" wire:loading.attr="disabled">
                <flux:icon class="size-24 rotate-12 absolute left-0 top-1/2 -translate-y-1/2" name="fingerprint" />
                <span wire:loading.remove wire:target="launchFingerprint">Fingerprint</span>
                <span wire:loading wire:target="launchFingerprint">
                    <flux:icon.loading class="size-8" />
                </span>
            </flux:button>
            <flux:button wire:click="$js.biometricBpjsTrigger('frista')"
                wire:target="checkBiometricStatus,launchFingerprint" variant="primary" color="rose" size="2xl"
                class="h-28 !text-5xl tracking-widest uppercase" wire:loading.attr="disabled">
                <flux:icon class="size-24 rotate-12 absolute left-0 top-1/2 -translate-y-1/2" name="camera" />
                <span wire:loading.remove wire:target="launchFingerprint">FRISTA</span>
                <span wire:loading wire:target="launchFrista">
                    <flux:icon.loading class="size-8" />
                </span>
            </flux:button>
        </div>
    @endif

    {{-- Tombol Lanjutkan - Hanya tampil jika validasi sukses (kode == 1) --}}
    @if (isset($biometricStatus['kode']) && $biometricStatus['kode'] == 1)
        <button wire:click="setBiometricStatus" wire:loading.attr="disabled"
            class="w-full h-20 rounded-2xl bg-gradient-to-br from-primary-700 to-primary-500 hover:to-yellow-300 active:scale-[0.99] transition-all duration-200 shadow-xl flex items-center justify-center gap-3 disabled:opacity-60 disabled:cursor-not-allowed">
            <svg wire:loading.remove wire:target="setBiometricStatus" class="size-8 text-secondary-300" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg wire:loading wire:target="setBiometricStatus" class="size-8 text-white animate-spin" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            <span wire:loading.remove wire:target="setBiometricStatus"
                class="text-3xl font-black text-secondary-300 uppercase tracking-widest drop-shadow">
                Lanjutkan
            </span>
            <span wire:loading wire:target="setBiometricStatus"
                class="text-3xl font-black text-white uppercase tracking-widest">
                Memproses...
            </span>
        </button>
    @endif
    {{-- <flux:button wire:target="setBiometricStatus" wire:click="setBiometricStatus" variant="primary" size="2xl"
        class="h-28 !text-5xl tracking-widest uppercase w-full">
        <flux:icon class="size-24 rotate-12 absolute left-0 top-1/2 -translate-y-1/2" name="check-circle" />
        Lewati (Dev Only)
    </flux:button> --}}
</div>

@script
    <script>
        $js('biometricBpjsTrigger', (mode) => {
            console.log('[INFO]', 'Biometric BPJS Kesehatan Trigger Test. Mode: ' + mode);
            window.postMessage({
                type: mode == 'fingerprint' ? 'BPJS_FINGER_TRIGGER' : 'BPJS_FRISTA_TRIGGER',
                mode: mode,
                noKartu: '{{ $participantData['peserta']['noKartu'] }}',
                username: '{{ $username }}',
                password: '{{ $password }}'
            }, '*');
        })
    </script>
@endscript
