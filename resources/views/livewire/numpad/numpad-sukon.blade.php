<div class="mx-auto relative w-full"
x-data="{
    value: @entangle('value'),
    maxLength: {{ $maxLength ?? 25 }},
    disabled: @entangle('disabled'),
    type: '{{ $type }}',
    terbilang: { '0':'nol','1':'satu','2':'dua','3':'tiga','4':'empat','5':'lima','6':'enam','7':'tujuh','8':'delapan','9':'sembilan' },
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
    }
}">
    @if($disabled)
        <div class="absolute inset-0 z-30 rounded-lg grid place-items-center cursor-wait">
            <flux:icon.loading />
        </div>
    @endif

    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    {{-- Display Input --}}
    <flux:input size="2xl" x-model="value" :placeholder="$placeholder" ::disabled="disabled" readonly
        class:input="text-center !border-primary-700 !text-primary-700"
        class='mb-4 font-bold' icon:trailing="pen" />

    {{-- Numpad Grid --}}
    <div class="grid grid-cols-3 gap-2 mx-auto">
        {{-- Row 1: 7 8 9 --}}
        <flux:button @click="addNumber('7')" ::disabled="disabled"
            variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            7
        </flux:button>
        <flux:button @click="addNumber('8')" ::disabled="disabled"
            variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            8
        </flux:button>
        <flux:button @click="addNumber('9')" ::disabled="disabled"
            variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            9
        </flux:button>

        {{-- Row 2: 4 5 6 --}}
        <flux:button @click="addNumber('4')" ::disabled="disabled"
            variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            4
        </flux:button>
        <flux:button @click="addNumber('5')" ::disabled="disabled"
            variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            5
        </flux:button>
        <flux:button @click="addNumber('6')" ::disabled="disabled"
            variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            6
        </flux:button>

        {{-- Row 3: 1 2 3 --}}
        <flux:button @click="addNumber('1')" ::disabled="disabled"
            variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            1
        </flux:button>
        <flux:button @click="addNumber('2')" ::disabled="disabled"
            variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            2
        </flux:button>
        <flux:button @click="addNumber('3')" ::disabled="disabled"
            variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            3
        </flux:button>

        {{-- Row 4: 0 and Backspace --}}
        <flux:button @click="addNumber('0')" ::disabled="disabled"
            variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            0
        </flux:button>
        <flux:button icon="arrow-left" @click="backspace"
            ::disabled="disabled" variant="outline" size="2xl"
            class="w-full col-span-2 !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">

        </flux:button>

        {{-- Row 5: Clear and Enter --}}
        <flux:button icon="eraser" @click="clear"
            ::disabled="disabled" variant="outline" size="2xl"
            class="w-full !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">

        </flux:button>
        <flux:button @click="enter" ::disabled="disabled"
            variant="outline" size="2xl"
            class="w-full col-span-2 !text-3xl font-semibold cursor-pointer active:bg-primary-700 active:text-secondary-300 active:border-secondary-300 hover:!bg-primary-700 hover:text-secondary-300 hover:!border-secondary-300">
            OK
        </flux:button>
    </div>
    @isset($slot)
        {{ $slot }}
    @endisset
</div>
