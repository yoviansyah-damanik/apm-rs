<div class="grid grid-cols-2 gap-4" x-data="{
    pending: null,
    resetTimeout: null,
    handleSelect(name, value, desc) {
        if (this.pending === name) {
            this.pending = null;
            clearTimeout(this.resetTimeout);
            $dispatch('speak', { text: 'Anda memilih ' + value + '.' });
            $wire.setPurposeOfVisit(name);
            return;
        }
        this.pending = name;
        $dispatch('speak-stop');
        $dispatch('speak', { text: value + '. ' + desc + '. Ketuk sekali lagi untuk melanjutkan.' });
        clearTimeout(this.resetTimeout);
    }
}"
    @speak-ended.window="if (pending !== null) { clearTimeout(resetTimeout); resetTimeout = setTimeout(() => { pending = null; }, 5000); }">
    @if (!empty($purposeOfVisits) && is_array($purposeOfVisits))
        @foreach ($purposeOfVisits as $purposeOfVisit)
            @php
                $name = $purposeOfVisit->name;
                $value = $purposeOfVisit->value;
                $desc = $purposeOfVisit->description();
                $icon = $purposeOfVisit->icon();
            @endphp
            <flux:button variant="ghost" wire:loading.attr="disabled" wire:target="setPurposeOfVisit"
                @click="handleSelect('{{ $name }}', '{{ $value }}', '{{ addslashes($desc) }}')"
                ::class="pending === '{{ $name }}' && 'ring-4 ring-secondary-300 !bg-yellow-400/20 scale-[1.03] shadow-[0_0_24px_4px_rgba(255,247,0,0.35)]'"
                class="drop-shadow-xl relative overflow-hidden !h-40 flex flex-col rounded-xl justify-center items-center text-center bg-gradient-to-br from-primary-700 to-primary-500 hover:to-yellow-300 active:to-yellow-300 backdrop-blur-2xl transition-all duration-300">
                <flux:heading class="!text-3xl uppercase !text-secondary-300 tracking-widest">
                    {{ $value }}
                </flux:heading>
                <flux:text class="text-white/80 font-light text-sm text-wrap leading-snug px-2">
                    {{ $desc }}
                </flux:text>

                {{-- Icon background --}}
                <flux:icon :name="$icon"
                    class="absolute size-36 -left-6 -z-10 text-white/10 top-1/2 -translate-y-1/2 h-full rotate-6" />

                {{-- Badge konfirmasi --}}
                <div x-show="pending === '{{ $name }}'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-3 scale-90"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute bottom-2 left-0 right-0 flex justify-center pointer-events-none">
                    <span
                        class="inline-flex items-center gap-1.5 bg-secondary-300 text-primary-900 text-xs font-bold px-3 py-1 rounded-full animate-bounce shadow-lg">
                        <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M13 9l3 3-3 3M5 12h12" />
                        </svg>
                        Ketuk lagi untuk lanjut
                    </span>
                </div>
            </flux:button>
        @endforeach
    @else
        <div class="col-span-2">
            <flux:callout variant="warning" icon="exclamation-triangle" heading="Data Tidak Tersedia">
                <flux:callout.text>
                    Tidak ada pilihan tujuan kunjungan yang tersedia. Silakan hubungi petugas.
                </flux:callout.text>
            </flux:callout>
        </div>
    @endif

    <div class="col-span-2 text-center text-white/60 text-sm py-1">
        Ketuk tombol <span class="text-secondary-300 font-semibold">1x</span> untuk mendengar informasi &nbsp;·&nbsp; Ketuk
        <span class="text-secondary-300 font-semibold">2x</span> untuk melanjutkan
    </div>
</div>
