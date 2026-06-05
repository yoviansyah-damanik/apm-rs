<div class="flex flex-col h-full gap-3">

    {{-- Header --}}
    <div class="px-1 pt-2 shrink-0 text-center">
        <p class="text-base font-bold uppercase tracking-[0.2em] text-secondary-300 mb-0.5">Informasi</p>
        <h1 class="text-4xl font-black text-white drop-shadow-lg uppercase">Jadwal Poliklinik</h1>
    </div>

    {{-- Date Navigator --}}
    <div class="shrink-0" wire:ignore x-data="{
        activeDate: '{{ $selectedDate }}',
        offset: 0,
        visibleCount: 7,
        dates: @js($dates),
        get visibleDates() { return this.dates.slice(this.offset, this.offset + this.visibleCount); },
        get canPrev() { return this.offset > 0; },
        get canNext() { return this.offset + this.visibleCount < this.dates.length; },
        prev() { if (this.canPrev) this.offset = Math.max(0, this.offset - this.visibleCount); },
        next() { if (this.canNext) this.offset = Math.min(this.dates.length - this.visibleCount, this.offset + this.visibleCount); },
    }">
        <div class="flex gap-2 h-full p-2">

            {{-- Prev --}}
            <button @click="prev()" :disabled="!canPrev"
                :class="canPrev ? 'bg-white text-primary-800 hover:bg-white shadow-sm' :
                    'bg-white/20 text-white/30 cursor-not-allowed'"
                class="w-10 h-full rounded-xl flex items-center justify-center transition-all shrink-0 font-bold">
                <flux:icon.chevron-left class="size-5" />
            </button>

            {{-- Date Tabs --}}
            <div class="flex flex-1 gap-1.5">
                <template x-for="d in visibleDates" :key="d.date">
                    <button
                        @click="activeDate = d.date; $dispatch('speak', {text: d.fullDate}); $wire.selectDate(d.date)"
                        :class="activeDate === d.date ?
                            'bg-primary-700 text-white shadow-lg shadow-primary-900/30 scale-105' :
                            'bg-white text-primary-900 hover:bg-white hover:shadow-md'"
                        class="relative overflow-hidden flex-1 flex flex-col items-center py-2.5 px-1 rounded-xl transition-all duration-200 min-w-0 backdrop-blur-sm">
                        <span x-text="d.dayName" class="text-base font-bold uppercase tracking-wider opacity-70"></span>
                        <span x-text="d.dayNum" class="text-3xl font-black leading-tight"></span>
                        <span x-text="d.month" class="text-base font-semibold opacity-60"></span>
                        <span x-show="d.isToday" :class="activeDate === d.date ? 'bg-secondary-300' : 'bg-primary-600'"
                            class="w-full h-1.5 absolute bottom-0 inset-x-0"></span>
                    </button>
                </template>
            </div>

            {{-- Next --}}
            <button @click="next()" :disabled="!canNext"
                :class="canNext ? 'bg-white text-primary-800 hover:bg-white shadow-sm' :
                    'bg-white/20 text-white/30 cursor-not-allowed'"
                class="w-10 h-full rounded-xl flex items-center justify-center transition-all shrink-0 font-bold">
                <flux:icon.chevron-right class="size-5" />
            </button>

        </div>
    </div>

    {{-- Schedule Panel --}}
    <div class="flex-1 overflow-hidden relative">

        {{-- Loading skeleton overlay --}}
        <div wire:loading wire:target="selectDate"
            class="absolute inset-0 z-20 rounded-2xl overflow-hidden bg-white shadow-xl flex flex-col">

            {{-- Header skeleton --}}
            <div class="px-6 py-4 shrink-0 bg-primary-700 flex items-center justify-between">
                <div class="flex flex-col gap-2">
                    <div class="h-5 w-52 bg-white/20 rounded-lg animate-pulse"></div>
                    <div class="h-3.5 w-20 bg-white/15 rounded-lg animate-pulse"></div>
                </div>
                <div class="flex gap-2">
                    <div class="h-8 w-24 bg-white/20 rounded-full animate-pulse"></div>
                    <div class="h-8 w-20 bg-white/20 rounded-full animate-pulse"></div>
                </div>
            </div>

            {{-- Column header skeleton --}}
            <div class="px-6 py-3 shrink-0 bg-primary-50 border-b border-primary-100 flex gap-6 items-center">
                <div class="flex-1 h-3 w-24 bg-primary-200 rounded animate-pulse"></div>
                <div class="flex-1 h-3 w-20 bg-primary-200 rounded animate-pulse"></div>
                <div class="w-36 h-3 bg-primary-200 rounded animate-pulse mx-auto"></div>
                <div class="w-28 h-3 bg-primary-200 rounded animate-pulse mx-auto"></div>
            </div>

            {{-- Row skeletons --}}
            <div class="flex-1 overflow-hidden divide-y divide-gray-100">
                @foreach ([['70%', '60%'], ['55%', '75%'], ['80%', '50%'], ['65%', '70%'], ['50%', '65%'], ['75%', '55%']] as $i => $widths)
                    <div class="px-6 py-4 flex gap-6 items-center {{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/70' }}">
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 rounded animate-pulse" style="width: {{ $widths[0] }}"></div>
                        </div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 rounded animate-pulse" style="width: {{ $widths[1] }}"></div>
                        </div>
                        <div class="w-36 flex justify-center">
                            <div class="h-7 w-32 bg-primary-100 rounded-full animate-pulse"></div>
                        </div>
                        <div class="w-28 flex flex-col items-center gap-2">
                            <div class="h-4 w-14 bg-gray-200 rounded animate-pulse"></div>
                            <div class="h-1.5 w-24 bg-gray-200 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        @if ($selectedDateInfo)
            @php
                $total = count($schedules);
                $available = collect($schedules)->where('sisa_kuota', '>', 0)->count();
                $fullCount = $total - $available;
            @endphp

            <div wire:loading.class="opacity-40 pointer-events-none" wire:target="selectDate"
                class="h-full flex flex-col rounded-2xl overflow-hidden shadow-xl bg-white/90 backdrop-blur-xl transition-opacity duration-150">

                {{-- Card Header --}}
                <div class="flex items-center justify-between px-6 py-4 shrink-0 bg-primary-700">
                    <div>
                        <p class="font-black text-white text-lg leading-tight mb-0">
                            {{ ucfirst(strtolower($selectedDateInfo['day'])) }},
                            {{ \Carbon\Carbon::parse($selectedDate)->locale('id')->isoFormat('D MMMM Y') }}
                        </p>
                        @if ($selectedDateInfo['isToday'])
                            <span class="text-base font-bold text-secondary-300 uppercase tracking-widest">
                                ● Hari ini
                            </span>
                        @endif
                    </div>

                    @if ($total > 0)
                        <div class="flex items-center gap-2">
                            @if ($available > 0)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 text-white text-sm font-bold border border-white/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-secondary-300 animate-pulse"></span>
                                    {{ $available }} Tersedia
                                </span>
                            @endif
                            @if ($fullCount > 0)
                                <span
                                    class="inline-flex items-center px-3 py-1.5 rounded-full bg-red-500/30 text-red-100 text-sm font-bold border border-red-300/30">
                                    {{ $fullCount }} Penuh
                                </span>
                            @endif
                            <span
                                class="inline-flex items-center px-3 py-1.5 rounded-full bg-white text-primary-700 text-sm font-semibold">
                                {{ $total }} Jadwal
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Table / Empty --}}
                @if ($total > 0)
                    <div class="flex-1 overflow-y-auto">
                        <table class="w-full text-left">
                            <thead class="sticky top-0 z-10 bg-primary-50 border-b border-primary-100">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-base font-bold text-primary-800 uppercase tracking-widest">
                                        Poliklinik</th>
                                    <th
                                        class="px-6 py-3 text-base font-bold text-primary-800 uppercase tracking-widest">
                                        Dokter</th>
                                    <th
                                        class="px-6 py-3 text-base font-bold text-primary-800 uppercase tracking-widest text-center">
                                        Jam Praktek</th>
                                    <th
                                        class="px-6 py-3 text-base font-bold text-primary-800 uppercase tracking-widest text-center">
                                        Kapasitas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($schedules as $i => $schedule)
                                    @php
                                        $kuota = $schedule['kuota'] ?? 0;
                                        $terpakai = $schedule['kuota_terpakai'] ?? 0;
                                        $sisa = $schedule['sisa_kuota'] ?? 0;
                                        $pct = $kuota > 0 ? round(($terpakai / $kuota) * 100) : 0;
                                        $full = $sisa <= 0;

                                        $nmPoli = $schedule['polyclinic']['nm_poli'] ?? '';
                                        $nmDokter = $schedule['doctor']['nm_dokter'] ?? '';
                                        $jamMulai = str_replace(
                                            ':',
                                            ' lewat ',
                                            substr($schedule['jam_mulai'] ?? '', 0, 5),
                                        );
                                        $jamSelesai = str_replace(
                                            ':',
                                            ' lewat ',
                                            substr($schedule['jam_selesai'] ?? '', 0, 5),
                                        );
                                        $kuotaText = $full ? 'Kuota sudah penuh.' : "Sisa kuota {$sisa} dari {$kuota}.";
                                        $speechText = "{$nmPoli}. {$nmDokter}. Jam praktek {$jamMulai} sampai {$jamSelesai}. {$kuotaText}";
                                    @endphp
                                    <tr @click="$dispatch('speak', {text: {{ Js::from($speechText) }}})"
                                        class="cursor-pointer transition-colors {{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/70' }} hover:bg-primary-50 active:bg-primary-100">
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-gray-800 text-base">
                                                {{ $schedule['polyclinic']['nm_poli'] ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-600 text-base">
                                                {{ $schedule['doctor']['nm_dokter'] ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="inline-flex items-center gap-1.5 text-base text-gray-700 font-medium bg-primary-50 border border-primary-100 px-3 py-1 rounded-full">
                                                <flux:icon.clock class="size-3.5 text-primary-600" />
                                                {{ substr($schedule['jam_mulai'] ?? '', 0, 5) }}
                                                –
                                                {{ substr($schedule['jam_selesai'] ?? '', 0, 5) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col items-center gap-1.5">
                                                @if ($full)
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-base font-bold bg-red-50 text-red-600 border border-red-200">
                                                        Penuh
                                                    </span>
                                                @else
                                                    <span class="text-base font-black text-primary-700">
                                                        {{ $terpakai }}
                                                        <span class="text-base font-normal text-gray-400">/
                                                            {{ $kuota }}</span>
                                                    </span>
                                                @endif
                                                <div class="w-24 h-1.5 bg-primary-100 rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-500
                                                        {{ $pct >= 100 ? 'bg-red-500' : ($pct >= 75 ? 'bg-amber-400' : 'bg-primary-500') }}"
                                                        style="width: {{ min($pct, 100) }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div wire:loading.class="opacity-0" wire:target="selectDate"
                        class="flex-1 flex flex-col items-center justify-center gap-5 px-8 transition-opacity duration-200">

                        {{-- Ikon berlapis --}}
                        <div class="relative">
                            <div
                                class="w-28 h-28 rounded-3xl bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center shadow-inner">
                                <flux:icon.calendar class="size-14 text-primary-300" />
                            </div>
                            <div
                                class="absolute -bottom-2 -right-2 w-10 h-10 rounded-xl bg-amber-50 border-2 border-white flex items-center justify-center shadow-sm">
                                <flux:icon.clock class="size-5 text-amber-400" />
                            </div>
                        </div>

                        {{-- Teks --}}
                        <div class="text-center">
                            <p class="font-black text-gray-700 text-xl leading-tight">Tidak Ada Jadwal</p>
                            <p class="text-base text-gray-400 mt-1.5 leading-relaxed">
                                Tidak ada jadwal praktek<br>
                                pada hari <span
                                    class="font-semibold text-primary-500">{{ ucfirst(strtolower($selectedDateInfo['day'])) }}</span>
                                ini
                            </p>
                        </div>

                        {{-- Dekorasi garis --}}
                        <div class="flex items-center gap-2 mt-1">
                            <div class="w-8 h-0.5 bg-gray-200 rounded-full"></div>
                            <div class="w-2 h-2 rounded-full bg-primary-200"></div>
                            <div class="w-8 h-0.5 bg-gray-200 rounded-full"></div>
                        </div>

                    </div>
                @endif

            </div>
        @else
            <div class="h-full flex flex-col items-center justify-center gap-6 px-8">

                {{-- Ikon berlapis di atas background gelap --}}
                <div class="relative">
                    <div
                        class="w-32 h-32 rounded-3xl bg-white/10 border border-white/20 flex items-center justify-center backdrop-blur-sm">
                        <flux:icon.archive-box-x-mark class="size-16 text-white/40" />
                    </div>
                    <div
                        class="absolute -bottom-2 -right-2 w-10 h-10 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center backdrop-blur-sm">
                        <flux:icon.calendar-days class="size-5 text-white/50" />
                    </div>
                </div>

                {{-- Teks --}}
                <div class="text-center">
                    <p class="font-black text-white/80 text-xl leading-tight">Jadwal Tidak Tersedia</p>
                    <p class="text-base text-white/40 mt-1.5 leading-relaxed">
                        Belum ada data jadwal<br>yang dapat ditampilkan
                    </p>
                </div>

                {{-- Dekorasi garis --}}
                <div class="flex items-center gap-2">
                    <div class="w-8 h-0.5 bg-white/20 rounded-full"></div>
                    <div class="w-2 h-2 rounded-full bg-white/30"></div>
                    <div class="w-8 h-0.5 bg-white/20 rounded-full"></div>
                </div>

            </div>
        @endif

    </div>

</div>
