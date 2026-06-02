<div class="space-y-6"
    x-data="{ showFaskesModal: false, showDiagnosisModal: false }"
    @close-faskes-modal.window="showFaskesModal = false"
    @close-diagnosis-modal.window="showDiagnosisModal = false"
    @date-picker-selected.window="$wire.set('tglRujukan', $event.detail.date)">

    {{-- Form Card --}}
    <div class="rounded-2xl shadow-xl">
        <div class="flex items-center gap-3 px-5 py-3 bg-primary-700">
            <flux:icon name="document-text" class="size-5 text-secondary-300" />
            <span class="text-sm font-bold text-white uppercase tracking-widest">Input Rujukan Manual</span>
        </div>

        <div class="p-5 bg-black/20 space-y-5">

            {{-- No Rujukan --}}
            <div>
                <label class="text-xs font-bold uppercase tracking-widest text-secondary-300 mb-1 block">
                    Nomor Rujukan
                </label>
                <input id="input-no-rujukan" type="text" wire:model="noRujukan"
                    placeholder="Contoh: 1234567890123456" autocomplete="off"
                    x-on:focus="$dispatch('virtual-keyboard-show', { targetId: $el.id })"
                    class="w-full h-14 px-4 rounded-xl bg-white/10 border border-white/20 text-white text-lg font-semibold
                           placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-secondary-300/60 cursor-pointer" />
                @error('noRujukan')
                    <div class="mt-1 text-red-400 text-sm">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tanggal Rujukan --}}
            <div>
                <label class="text-xs font-bold uppercase tracking-widest text-secondary-300 mb-1 block">
                    Tanggal Rujukan
                </label>
                <button type="button" @click="$dispatch('date-picker-show', { value: '{{ $tglRujukan }}' })"
                    class="w-full h-14 px-4 rounded-xl text-left flex items-center gap-3 transition-all
                           bg-white/10 border focus:outline-none focus:ring-2 focus:ring-secondary-300/60
                           {{ $tglRujukan ? 'border-white/40' : 'border-white/20' }}">
                    <flux:icon name="calendar" class="size-5 shrink-0 text-secondary-300/70" />
                    @if ($tglRujukan)
                        <span class="text-white font-semibold text-lg">
                            {{ \Carbon\Carbon::parse($tglRujukan)->translatedFormat('d F Y') }}
                        </span>
                    @else
                        <span class="text-white/40 text-base">Pilih tanggal...</span>
                    @endif
                </button>
                @error('tglRujukan')
                    <div class="mt-1 text-red-400 text-sm">{{ $message }}</div>
                @enderror
            </div>

            {{-- Asal Faskes --}}
            <div>
                <label class="text-xs font-bold uppercase tracking-widest text-secondary-300 mb-1 block">
                    Asal Faskes
                </label>
                <button type="button" @click="showFaskesModal = true"
                    class="w-full h-14 px-4 rounded-xl text-left transition-all
                        {{ $asalFaskesKode
                            ? 'bg-primary-700/50 border border-primary-500/50 text-white'
                            : 'bg-white/10 border border-white/20 text-white/40' }}">
                    @if ($asalFaskesKode)
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-white text-sm leading-tight">{{ $asalFaskesNama }}</span>
                            <span
                                class="shrink-0 text-xs px-2 py-0.5 rounded-full
                                {{ $asalFaskesJenis === 'FKTP' ? 'bg-blue-500/30 text-blue-300' : 'bg-purple-500/30 text-purple-300' }}">
                                {{ $asalFaskesJenis }}
                            </span>
                        </div>
                        <div class="text-xs text-white/50 mt-0.5">{{ $asalFaskesKode }}</div>
                    @else
                        Pilih faskes asal rujukan...
                    @endif
                </button>
                @error('asalFaskesKode')
                    <div class="mt-1 text-red-400 text-sm">{{ $message }}</div>
                @enderror
            </div>

            {{-- Diagnosis --}}
            <div>
                <label class="text-xs font-bold uppercase tracking-widest text-secondary-300 mb-1 block">
                    Diagnosa (ICD-10)
                </label>
                <button type="button" @click="showDiagnosisModal = true"
                    class="w-full h-14 px-4 rounded-xl text-left transition-all
                        {{ $diagnosisKode
                            ? 'bg-primary-700/50 border border-primary-500/50 text-white'
                            : 'bg-white/10 border border-white/20 text-white/40' }}">
                    @if ($diagnosisKode)
                        <div class="font-semibold text-white text-sm leading-tight truncate">{{ $diagnosisNama }}</div>
                        <div class="text-xs text-white/50 mt-0.5">{{ $diagnosisKode }}</div>
                    @else
                        Pilih diagnosis (ICD-10)...
                    @endif
                </button>
            </div>

            {{-- Tombol Submit --}}
            <button wire:click="submit" wire:loading.attr="disabled"
                class="w-full h-16 rounded-2xl bg-gradient-to-br from-primary-700 to-primary-500 hover:to-yellow-300
                       active:scale-[0.99] transition-all duration-200 shadow-xl
                       flex items-center justify-center gap-3
                       disabled:opacity-60 disabled:cursor-not-allowed">
                <flux:icon name="check-circle" class="size-7 text-secondary-300" />
                <span class="text-2xl font-black text-secondary-300 uppercase tracking-widest drop-shadow">Lanjutkan</span>
            </button>

        </div>
    </div>

    {{-- Modal Pilih Faskes --}}
    <div x-show="showFaskesModal" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-start justify-center p-6">
        <div class="absolute inset-0 bg-black/60" @click="showFaskesModal = false"></div>
        <div x-show="showFaskesModal" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 bg-primary-700">
                <div class="flex items-center gap-3">
                    <flux:icon name="building-office-2" class="size-5 text-secondary-300" />
                    <span class="text-sm font-bold text-white uppercase tracking-widest">Pilih Asal Faskes</span>
                </div>
                <button type="button" @click="showFaskesModal = false"
                    class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                    <flux:icon name="x-mark" class="size-4 text-white" />
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex gap-2">
                    <input id="input-search-faskes" type="text" wire:model="faskesQuery"
                        wire:keydown.enter="searchFaskes" placeholder="Ketik nama atau kode faskes..."
                        autocomplete="off" x-on:focus="$dispatch('virtual-keyboard-show', { targetId: $el.id })"
                        class="flex-1 h-11 px-4 rounded-xl border border-gray-300 text-gray-900 text-sm
                               focus:outline-none focus:ring-2 focus:ring-primary-700/50 focus:border-primary-700" />
                    <button wire:click="searchFaskes" wire:loading.attr="disabled" wire:target="searchFaskes"
                        class="h-11 px-5 rounded-xl bg-primary-700 hover:bg-primary-600 text-white font-bold
                               flex items-center gap-2 transition-colors disabled:opacity-60 shrink-0">
                        <flux:icon name="magnifying-glass" class="size-4" wire:loading.remove
                            wire:target="searchFaskes" />
                        <flux:icon name="arrow-path" class="size-4 hidden"
                            wire:loading.class="!inline-block animate-spin" wire:target="searchFaskes" />
                        Cari
                    </button>
                </div>
                @if ($faskesError)
                    <div
                        class="flex items-center gap-2 text-amber-600 text-sm bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                        <flux:icon name="exclamation-triangle" class="size-4 shrink-0" />
                        {{ $faskesError }}
                    </div>
                @endif
                @if (!empty($faskesResults))
                    <div class="max-h-64 overflow-y-auto space-y-1.5 pr-1">
                        @foreach ($faskesResults as $faskes)
                            <button type="button"
                                wire:click="selectFaskes('{{ $faskes['kode'] }}', '{{ addslashes($faskes['nama']) }}', '{{ $faskes['jenis'] }}')"
                                class="w-full text-left px-4 py-3 rounded-xl border transition-all
                                       bg-gray-50 hover:bg-primary-50 border-gray-200 hover:border-primary-400
                                       active:bg-primary-100 active:border-primary-500">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-gray-900 font-semibold text-sm truncate">
                                            {{ $faskes['nama'] }}</div>
                                        <div class="text-gray-400 text-xs mt-0.5">{{ $faskes['kode'] }}</div>
                                    </div>
                                    <span
                                        class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full
                                        {{ $faskes['jenis'] === 'FKTP' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                        {{ $faskes['jenis'] }}
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Pilih Diagnosis --}}
    <div x-show="showDiagnosisModal" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-start justify-center p-6">
        <div class="absolute inset-0 bg-black/60" @click="showDiagnosisModal = false"></div>
        <div x-show="showDiagnosisModal" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 bg-primary-700">
                <div class="flex items-center gap-3">
                    <flux:icon name="clipboard-document-list" class="size-5 text-secondary-300" />
                    <span class="text-sm font-bold text-white uppercase tracking-widest">Pilih Diagnosis
                        (ICD-10)</span>
                </div>
                <button type="button" @click="showDiagnosisModal = false"
                    class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                    <flux:icon name="x-mark" class="size-4 text-white" />
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex gap-2">
                    <input id="input-search-diagnosis" type="text" wire:model="diagnosisQuery"
                        wire:keydown.enter="searchDiagnosis" placeholder="Ketik kode atau nama diagnosis..."
                        autocomplete="off" x-on:focus="$dispatch('virtual-keyboard-show', { targetId: $el.id })"
                        class="flex-1 h-11 px-4 rounded-xl border border-gray-300 text-gray-900 text-sm
                               focus:outline-none focus:ring-2 focus:ring-primary-700/50 focus:border-primary-700" />
                    <button wire:click="searchDiagnosis" wire:loading.attr="disabled" wire:target="searchDiagnosis"
                        class="h-11 px-5 rounded-xl bg-primary-700 hover:bg-primary-600 text-white font-bold
                               flex items-center gap-2 transition-colors disabled:opacity-60 shrink-0">
                        <flux:icon name="magnifying-glass" class="size-4" wire:loading.remove
                            wire:target="searchDiagnosis" />
                        <flux:icon name="arrow-path" class="size-4 hidden"
                            wire:loading.class="!inline-block animate-spin" wire:target="searchDiagnosis" />
                        Cari
                    </button>
                </div>
                @if ($diagnosisError)
                    <div
                        class="flex items-center gap-2 text-amber-600 text-sm bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                        <flux:icon name="exclamation-triangle" class="size-4 shrink-0" />
                        {{ $diagnosisError }}
                    </div>
                @endif
                @if (!empty($diagnosisResults))
                    <div class="max-h-72 overflow-y-auto space-y-1.5 pr-1">
                        @foreach ($diagnosisResults as $dx)
                            <button type="button"
                                wire:click="selectDiagnosis('{{ $dx['kd_penyakit'] }}', '{{ addslashes($dx['nm_penyakit']) }}')"
                                class="w-full text-left px-4 py-3 rounded-xl border transition-all
                                       bg-gray-50 hover:bg-primary-50 border-gray-200 hover:border-primary-400
                                       active:bg-primary-100 active:border-primary-500">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="shrink-0 text-xs font-bold px-2 py-1 rounded-lg bg-primary-100 text-primary-800 font-mono">
                                        {{ $dx['kd_penyakit'] }}
                                    </span>
                                    <span class="text-gray-900 text-sm truncate">{{ $dx['nm_penyakit'] }}</span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Virtual Keyboard --}}
    <livewire:numpad.virtual-keyboard />

    {{-- Date Picker --}}
    <livewire:numpad.date-picker />
</div>
