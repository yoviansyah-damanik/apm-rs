<div x-data="{
    show: false,
    selected: null,
    viewYear: new Date().getFullYear(),
    viewMonth: new Date().getMonth(),
    dayNames: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
    monthNames: ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],

    get label() {
        if (!this.selected) return '';
        const d = new Date(this.selected + 'T00:00:00');
        return d.getDate() + ' ' + this.monthNames[d.getMonth()] + ' ' + d.getFullYear();
    },

    get days() {
        const first = new Date(this.viewYear, this.viewMonth, 1).getDay();
        const total = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
        const cells = [];
        for (let i = 0; i < first; i++) cells.push(null);
        for (let d = 1; d <= total; d++) cells.push(d);
        return cells;
    },

    isToday(d) {
        if (!d) return false;
        const t = new Date();
        return d === t.getDate() && this.viewMonth === t.getMonth() && this.viewYear === t.getFullYear();
    },

    isSelected(d) {
        if (!d || !this.selected) return false;
        const s = new Date(this.selected + 'T00:00:00');
        return d === s.getDate() && this.viewMonth === s.getMonth() && this.viewYear === s.getFullYear();
    },

    pad(n) { return String(n).padStart(2, '0'); },

    select(d) {
        if (!d) return;
        this.selected = this.viewYear + '-' + this.pad(this.viewMonth + 1) + '-' + this.pad(d);
        $dispatch('date-picker-selected', { date: this.selected });
        this.show = false;
    },

    prevMonth() {
        if (this.viewMonth === 0) { this.viewMonth = 11; this.viewYear--; }
        else this.viewMonth--;
    },

    nextMonth() {
        if (this.viewMonth === 11) { this.viewMonth = 0; this.viewYear++; }
        else this.viewMonth++;
    },

    open(value) {
        this.selected = value || null;
        if (this.selected) {
            const d = new Date(this.selected + 'T00:00:00');
            this.viewYear = d.getFullYear();
            this.viewMonth = d.getMonth();
        } else {
            this.viewYear = new Date().getFullYear();
            this.viewMonth = new Date().getMonth();
        }
        this.show = true;
    }
}"
    @date-picker-show.window="open($event.detail.value)"
    @date-picker-hide.window="show = false">

    <div x-show="show" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9998] flex items-center justify-center p-4">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="show = false"></div>

        {{-- Card --}}
        <div x-show="show"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="relative w-full max-w-md bg-gradient-to-b from-[#0d1f10] to-[#081208]
                   border border-secondary-300/20 rounded-3xl shadow-2xl overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 bg-primary-700/90 border-b border-primary-600">
                <button type="button" @click="prevMonth()"
                    class="w-11 h-11 rounded-xl flex items-center justify-center
                           bg-white/10 hover:bg-white/25 active:bg-white/30 text-white transition-colors">
                    <flux:icon name="chevron-left" class="size-5" />
                </button>

                <div class="text-center">
                    <div class="text-secondary-300 font-black text-xl tracking-wide" x-text="monthNames[viewMonth]"></div>
                    <div class="text-white/70 text-sm font-semibold" x-text="viewYear"></div>
                </div>

                <button type="button" @click="nextMonth()"
                    class="w-11 h-11 rounded-xl flex items-center justify-center
                           bg-white/10 hover:bg-white/25 active:bg-white/30 text-white transition-colors">
                    <flux:icon name="chevron-right" class="size-5" />
                </button>
            </div>

            {{-- Body --}}
            <div class="p-5">

                {{-- Selected date info --}}
                <div class="mb-4 h-9 flex items-center justify-center">
                    <template x-if="selected">
                        <div class="flex items-center gap-2 px-4 py-1.5 rounded-xl bg-primary-700/30 border border-primary-600/40">
                            <flux:icon name="calendar-days" class="size-4 text-secondary-300" />
                            <span class="text-secondary-300 font-bold text-sm" x-text="label"></span>
                        </div>
                    </template>
                    <template x-if="!selected">
                        <span class="text-white/30 text-sm">Pilih tanggal</span>
                    </template>
                </div>

                {{-- Day name headers --}}
                <div class="grid grid-cols-7 mb-2">
                    <template x-for="name in dayNames" :key="name">
                        <div class="text-center text-[11px] font-bold uppercase tracking-widest text-secondary-300/50 py-1"
                            x-text="name"></div>
                    </template>
                </div>

                {{-- Day grid --}}
                <div class="grid grid-cols-7 gap-1">
                    <template x-for="(day, i) in days" :key="i">
                        <button type="button" @click="select(day)" :disabled="!day"
                            :class="{
                                'invisible pointer-events-none': !day,
                                'ring-2 ring-secondary-300/80 ring-offset-1 ring-offset-transparent text-white': isToday(day) && !isSelected(day),
                                'bg-primary-600 text-secondary-300 font-black shadow-lg shadow-primary-900/50': isSelected(day),
                                'text-white/80 hover:bg-white/10 active:bg-white/20': day && !isSelected(day),
                            }"
                            class="w-full aspect-square rounded-xl text-base font-semibold transition-all duration-100
                                   flex items-center justify-center">
                            <span x-text="day"></span>
                        </button>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="mt-5 flex gap-3">
                    <button type="button" @click="show = false"
                        class="flex-1 h-12 rounded-xl border border-white/20 text-white/60 font-semibold text-sm
                               hover:bg-white/10 active:bg-white/15 transition-colors">
                        Batal
                    </button>
                    <button type="button" @click="
                            const t = new Date();
                            viewYear = t.getFullYear();
                            viewMonth = t.getMonth();
                            select(t.getDate());
                        "
                        class="flex-1 h-12 rounded-xl bg-white/10 border border-secondary-300/30 text-secondary-300 font-bold text-sm
                               hover:bg-white/20 active:bg-white/25 transition-colors">
                        Hari Ini
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
