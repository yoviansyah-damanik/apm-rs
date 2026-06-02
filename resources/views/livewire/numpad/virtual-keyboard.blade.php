<div x-data="{
    show: false,
    targetId: null,
    terbilang: {
        '0': 'nol',
        '1': 'satu',
        '2': 'dua',
        '3': 'tiga',
        '4': 'empat',
        '5': 'lima',
        '6': 'enam',
        '7': 'tujuh',
        '8': 'delapan',
        '9': 'sembilan'
    },

    type(char) {
        const el = document.getElementById(this.targetId);
        if (!el) return;
        const s = el.selectionStart ?? el.value.length;
        const e = el.selectionEnd ?? el.value.length;
        el.value = el.value.slice(0, s) + char + el.value.slice(e);
        el.selectionStart = el.selectionEnd = s + char.length;
        el.dispatchEvent(new Event('input', { bubbles: true }));
        $dispatch('speak', { text: this.terbilang[char] ?? char.toLowerCase() });
    },

    backspace() {
        const el = document.getElementById(this.targetId);
        if (!el) return;
        const s = el.selectionStart ?? el.value.length;
        const e = el.selectionEnd ?? el.value.length;
        if (s !== e) {
            el.value = el.value.slice(0, s) + el.value.slice(e);
            el.selectionStart = el.selectionEnd = s;
        } else if (s > 0) {
            el.value = el.value.slice(0, s - 1) + el.value.slice(s);
            el.selectionStart = el.selectionEnd = s - 1;
        }
        el.dispatchEvent(new Event('input', { bubbles: true }));
        $dispatch('speak', { text: 'hapus' });
    },

    clear() {
        const el = document.getElementById(this.targetId);
        if (!el) return;
        el.value = '';
        el.dispatchEvent(new Event('input', { bubbles: true }));
        $dispatch('speak', { text: 'hapus semua' });
    },

    blurHandler: null,

    attachBlur(id) {
        this.detachBlur();
        const el = document.getElementById(id);
        if (!el) return;
        this.blurHandler = () => {
            this.show = false;
            this.targetId = null;
        };
        el.addEventListener('blur', this.blurHandler);
    },

    detachBlur() {
        if (!this.blurHandler || !this.targetId) return;
        const el = document.getElementById(this.targetId);
        if (el) el.removeEventListener('blur', this.blurHandler);
        this.blurHandler = null;
    },

    done() {
        this.detachBlur();
        this.show = false;
        this.targetId = null;
        $dispatch('speak', { text: 'OK' });
    }
}"
    @virtual-keyboard-show.window="
        targetId = $event.detail.targetId;
        show = true;
        $nextTick(() => attachBlur(targetId));
    "
    @virtual-keyboard-hide.window="detachBlur(); show = false; targetId = null">
    <div x-show="show" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0" @mousedown.stop.prevent @click.stop @pointerdown.stop.prevent
        class="fixed bottom-0 left-0 right-0 bg-white border-t-2 border-primary-700 shadow-2xl px-3 pt-3 pb-4 !z-[9999]">
        @php
            $btn = "w-full !text-xl font-semibold cursor-pointer
                    active:bg-primary-700 active:!text-secondary-300 active:!border-primary-700
                    hover:!bg-primary-700 hover:!text-secondary-300 hover:!border-primary-700";
            $btnDel = "w-full cursor-pointer
                       active:bg-red-600 active:!text-white active:!border-red-600
                       hover:!bg-red-600 hover:!text-white hover:!border-red-600";
            $btnNum = "w-full !text-2xl font-semibold cursor-pointer
                       active:bg-primary-700 active:!text-secondary-300 active:!border-primary-700
                       hover:!bg-primary-700 hover:!text-secondary-300 hover:!border-primary-700";
        @endphp

        <div class="flex gap-2">

            {{-- ===== QWERTY (Kiri, flex-1) ===== --}}
            <div class="flex-1 space-y-1.5">

                {{-- Baris 1: Q W E R T Y U I O P  [⌫] --}}
                {{-- Grid 11 kolom, masing-masing 1 kolom --}}
                <div class="grid grid-cols-11 gap-1.5">
                    @foreach (['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P'] as $k)
                        <flux:button @click="type('{{ $k }}')" variant="outline" size="2xl"
                            class="{{ $btn }}">{{ $k }}</flux:button>
                    @endforeach
                    <flux:button icon="arrow-left" @click="backspace()" variant="outline" size="2xl"
                        class="{{ $btnDel }}"></flux:button>
                </div>

                {{-- Baris 2: [indent] A S D F G H J K L [indent] --}}
                {{-- Grid 11 kolom, huruf di tengah (1 spacer kiri + 9 huruf + 1 spacer kanan) --}}
                <div class="grid grid-cols-11 gap-1.5">
                    @foreach (['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L'] as $k)
                        <flux:button @click="type('{{ $k }}')" variant="outline" size="2xl"
                            class="{{ $btn }}">{{ $k }}</flux:button>
                    @endforeach
                    <div></div>
                </div>

                {{-- Baris 3: Z X C V B N M  [CLR]  [OK] --}}
                {{-- Grid 11 kolom: 7 huruf + CLR(2) + OK(2) = 11 --}}
                <div class="grid grid-cols-11 gap-1.5">
                    @foreach (['Z', 'X', 'C', 'V', 'B', 'N', 'M'] as $k)
                        <flux:button @click="type('{{ $k }}')" variant="outline" size="2xl"
                            class="{{ $btn }}">{{ $k }}</flux:button>
                    @endforeach
                    <flux:button icon="eraser" @click="clear()" variant="outline" size="2xl"
                        class="col-span-2 w-full cursor-pointer
                               active:bg-yellow-500 active:!text-white active:!border-yellow-500
                               hover:!bg-yellow-500 hover:!text-white hover:!border-yellow-500">
                    </flux:button>
                    <flux:button @click="done()" variant="filled" size="2xl"
                        class="col-span-2 w-full cursor-pointer !bg-primary-700 !text-secondary-300 !border-primary-700
                               hover:!bg-primary-800 active:!bg-primary-900 font-black !text-lg">
                        OK
                    </flux:button>
                </div>

                {{-- Baris 4: [/] [.] [-] [SPASI(5)] [TUTUP(3)] = 11 --}}
                <div class="grid grid-cols-11 gap-1.5">
                    @foreach(['/', '.', '-'] as $k)
                        <flux:button @click="type('{{ $k }}')" variant="outline" size="2xl"
                            class="{{ $btn }}">{{ $k }}</flux:button>
                    @endforeach
                    <flux:button @click="type(' ')" variant="outline" size="2xl"
                        class="col-span-5 w-full cursor-pointer font-semibold uppercase tracking-widest !text-sm
                               active:bg-primary-700 active:!text-secondary-300 active:!border-primary-700
                               hover:!bg-primary-700 hover:!text-secondary-300 hover:!border-primary-700">
                        Spasi
                    </flux:button>
                    <flux:button @click="done()" variant="outline" size="2xl"
                        class="col-span-3 w-full cursor-pointer font-bold uppercase tracking-widest !text-sm
                               active:bg-primary-700 active:!text-secondary-300 active:!border-primary-700
                               hover:!bg-primary-700 hover:!text-secondary-300 hover:!border-primary-700">
                        Tutup
                    </flux:button>
                </div>

            </div>

            {{-- Pemisah --}}
            <div class="w-px self-stretch bg-primary-200 rounded"></div>

            {{-- ===== Numpad (Kanan) ===== --}}
            <div class="grid grid-cols-3 gap-1.5 content-start w-44">

                {{-- Baris 7 8 9 --}}
                @foreach (['7', '8', '9', '4', '5', '6', '1', '2', '3'] as $n)
                    <flux:button @click="type('{{ $n }}')" variant="outline" size="2xl"
                        class="{{ $btnNum }}">{{ $n }}</flux:button>
                @endforeach

                {{-- Baris 0 + ⌫ --}}
                <flux:button @click="type('0')" variant="outline" size="2xl"
                    class="col-span-2 {{ $btnNum }}">
                    0
                </flux:button>
                <flux:button icon="arrow-left" @click="backspace()" variant="outline" size="2xl"
                    class="{{ $btnDel }}"></flux:button>

            </div>

        </div>
    </div>
</div>
