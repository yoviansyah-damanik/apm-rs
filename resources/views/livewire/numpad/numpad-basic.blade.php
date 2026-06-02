<div class="mx-auto relative w-full" x-data="{
    value: @entangle('value'),
    maxLength: {{ $maxLength ?? 'null' }},
    disabled: @entangle('disabled'),
    autoDetect: {{ $autoDetect ? 'true' : 'false' }},
    digitTypes: {
        6: 'RM',
        13: 'BPJS',
        16: 'NIK'
    },
    get currentLength() {
        return this.value.length;
    },
    get detectedType() {
        return this.digitTypes[this.currentLength] || '';
    },
    isTypeMatch(length) {
        return this.currentLength === length;
    },
    terbilang: { '0': 'nol', '1': 'satu', '2': 'dua', '3': 'tiga', '4': 'empat', '5': 'lima', '6': 'enam', '7': 'tujuh', '8': 'delapan', '9': 'sembilan' },
    addNumber(number) {
        if (this.disabled) return;
        if (this.maxLength && this.value.length >= this.maxLength) return;
        this.value += number;
        $dispatch('speak', { text: this.terbilang[number] || number });
    },
    backspace() {
        if (this.disabled) return;
        this.value = this.value.slice(0, -1);
        $dispatch('speak', { text: 'hapus' });
    },
    clear() {
        if (this.disabled) return;
        this.value = '';
        $dispatch('speak', { text: 'hapus semua' });
    },
    enter() {
        if (this.disabled) return;
        $dispatch('speak', { text: 'OK' });
        $wire.enter();
    },
    scanning: false,
    scanTimeout: null,
    startScan() {
        if (this.disabled) return;
        this.scanning = true;
        $dispatch('speak', { text: 'Silahkan dekatkan kartu ke alat scan' });
        clearTimeout(this.scanTimeout);
        this.scanTimeout = setTimeout(() => { this.scanning = false; }, 10000);
        $nextTick(() => {
            const input = $el.querySelector('input[type=text], input:not([type])');
            if (input) input.focus();
        });
    },
    cancelScan() {
        this.scanning = false;
        clearTimeout(this.scanTimeout);
        $dispatch('speak', { text: 'Scan dibatalkan' });
    }
}" @barcode-detected.window="scanning = false; clearTimeout(scanTimeout)">
    @if ($disabled)
        <div class="absolute inset-0 z-30 rounded-lg grid place-items-center cursor-wait">
            <flux:icon.loading />
        </div>
    @endif
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    {{-- Auto Detect Indicators --}}
    @if ($autoDetect)
        <div class="flex flex-col items-center justify-center gap-1 mb-4">
            <div class="text-secondary-300 text-3xl mb-3 uppercase font-bold text-center">
                Silahkan masukkan nomor identitas anda
            </div>
            <div class="flex justify-between w-full overflow-hidden rounded-lg">
                <div class="flex-1 py-4 font-semibold text-center"
                    x-bind:class="isTypeMatch(6) ? 'bg-primary-100 text-primary-700' : 'text-gray-500 bg-gray-50'">Rekam
                    Medis
                    #6</div>
                <div class="flex-1 py-4 font-semibold text-center text-gray-500 bg-gray-50"
                    x-bind:class="isTypeMatch(13) ? 'bg-primary-100 text-primary-700' : 'text-gray-500 bg-gray-50'">BPJS #13
                </div>
                <div class="flex-1 py-4 font-semibold text-center text-gray-500 bg-gray-50"
                    x-bind:class="isTypeMatch(16) ? 'bg-primary-100 text-primary-700' : 'text-gray-500 bg-gray-50'">NIK #16
                </div>
            </div>
        </div>
    @endif

    {{-- Scan Overlay Instruksi --}}
    @if(env('BARCODE_SCANNER', true))
    <div x-show="scanning" x-transition
        class="absolute inset-0 z-40 bg-white/97 backdrop-blur-sm rounded-xl flex flex-col items-center justify-center gap-5 p-6">
        <div class="w-24 h-24 rounded-2xl bg-primary-700 flex items-center justify-center shadow-lg">
            <flux:icon.qr-code class="size-14 text-secondary-300 animate-pulse" />
        </div>
        <div class="text-center space-y-1">
            <p class="text-2xl font-black text-primary-800 uppercase tracking-wide">Scan Barcode</p>
            <p class="text-primary-700 font-semibold">Dekatkan kartu atau barcode ke alat scan</p>
            <p class="text-gray-400 text-sm mt-2">Kartu BPJS, KTP, atau dokumen identitas</p>
        </div>
        <button @click="cancelScan()"
            class="mt-2 py-3 px-8 rounded-xl flex items-center justify-center gap-2 font-bold text-base bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 active:bg-red-200 transition-all duration-200">
            <flux:icon.x-mark class="size-5" />
            Batal
        </button>
    </div>
    @endif

    {{-- Display Input --}}
    <flux:input size="2xl" x-model="value" :placeholder="$placeholder" ::disabled="disabled" ::readonly="!scanning"
        class:input="{{ 'text-center ' . ($value && $isInvalid ? '!border-red-700 !text-red-700' : '!border-primary-700 !text-primary-700') }}"
        class='mb-4 font-bold' icon:trailing="{{ !$value ? 'pen' : ($isInvalid ? 'x-circle' : 'check-circle') }}" />

    {{-- Scan Button --}}
    @if(env('BARCODE_SCANNER', true))
    <div class="flex gap-2 mb-3">
        <button @click="startScan()" :disabled="disabled || scanning"
            :class="scanning
                ? 'bg-primary-700 text-secondary-300 ring-2 ring-secondary-300 ring-offset-1 animate-pulse'
                : 'bg-white text-primary-700 border border-primary-200 hover:bg-primary-50 active:bg-primary-100'"
            class="flex-1 py-3 px-4 rounded-xl flex items-center justify-center gap-3 font-bold text-lg transition-all duration-300 disabled:opacity-40 disabled:cursor-not-allowed">
            <flux:icon.qr-code class="size-6 shrink-0" />
            <span x-text="scanning ? 'Menunggu scan...' : 'Scan Kartu'"></span>
        </button>
        <button x-show="scanning" x-transition @click="cancelScan()"
            class="py-3 px-5 rounded-xl flex items-center justify-center gap-2 font-bold text-base bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 active:bg-red-200 transition-all duration-200 shrink-0">
            <flux:icon.x-mark class="size-5" />
            Batal
        </button>
    </div>
    @endif

    {{-- Numpad Grid --}}
    <div class="grid grid-cols-3 gap-2 mx-auto">
        {{-- Row 1: 7 8 9 --}}
        <flux:button @click="addNumber('7')" ::disabled="disabled" variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            7
        </flux:button>
        <flux:button @click="addNumber('8')" ::disabled="disabled" variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            8
        </flux:button>
        <flux:button @click="addNumber('9')" ::disabled="disabled" variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            9
        </flux:button>

        {{-- Row 2: 4 5 6 --}}
        <flux:button @click="addNumber('4')" ::disabled="disabled" variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            4
        </flux:button>
        <flux:button @click="addNumber('5')" ::disabled="disabled" variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            5
        </flux:button>
        <flux:button @click="addNumber('6')" ::disabled="disabled" variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            6
        </flux:button>

        {{-- Row 3: 1 2 3 --}}
        <flux:button @click="addNumber('1')" ::disabled="disabled" variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            1
        </flux:button>
        <flux:button @click="addNumber('2')" ::disabled="disabled" variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            2
        </flux:button>
        <flux:button @click="addNumber('3')" ::disabled="disabled" variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            3
        </flux:button>

        {{-- Row 4: 0 and Backspace --}}
        <flux:button @click="addNumber('0')" ::disabled="disabled" variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            0
        </flux:button>
        <flux:button icon="arrow-left" @click="backspace" ::disabled="disabled" variant="outline" size="2xl"
            class="w-full col-span-2 !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">

        </flux:button>

        {{-- Row 5: Clear and Enter --}}
        <flux:button icon="eraser" @click="clear" ::disabled="disabled" variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">

        </flux:button>
        <flux:button @click="enter" ::disabled="disabled" variant="outline" size="2xl"
            class="w-full col-span-2 !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            OK
        </flux:button>
    </div>
    @isset($slot)
        {{ $slot }}
    @endisset
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let barcodeBuffer = '';
            let barcodeTimeout;

            document.addEventListener('keypress', function(e) {
                clearTimeout(barcodeTimeout);
                barcodeBuffer += e.key;

                barcodeTimeout = setTimeout(function() {
                    if (barcodeBuffer.length > 5) { // Asumsi barcode minimal 6 karakter
                        @this.updateBarcode(barcodeBuffer);
                        window.dispatchEvent(new CustomEvent('barcode-detected'));
                        barcodeBuffer = '';
                    } else {
                        barcodeBuffer = '';
                    }
                }, 100); // 100ms timeout
            });
        });
    </script>
@endpush
