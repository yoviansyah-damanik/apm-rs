<div class="space-y-6">
    {{-- Rujukan FKTP --}}
    @if (!empty($listOfReferences['fktp']))
        <div class="rounded-2xl overflow-hidden shadow-xl">
            <div class="flex items-center gap-3 px-5 py-3 bg-primary-700">
                <flux:icon name="document-text" class="size-5 text-secondary-300" />
                <span class="text-sm font-bold text-white uppercase tracking-widest">Rujukan FKTP</span>
            </div>
            <div class="flex flex-col gap-3 p-4 bg-black/20">
                @foreach ($listOfReferences['fktp'] as $reference)
                    @php
                        $disabled = $reference['isExpired'] || $reference['hasUsed'];
                        $jsonRef = htmlspecialchars(json_encode($reference), ENT_QUOTES, 'UTF-8');
                    @endphp
                    <button
                        @if (!$disabled) wire:loading.attr="disabled"
                            wire:target="selectReference"
                            x-on:click="$wire.selectReference('{{ $jsonRef }}', 'fktp')" @endif
                        @disabled($disabled)
                        class="w-full text-left rounded-xl transition-all duration-200 active:scale-[0.99]
                            {{ $disabled
                                ? 'bg-gray-800 border border-gray-700 cursor-not-allowed pointer-events-none'
                                : 'bg-gradient-to-br from-primary-700 to-primary-500 hover:to-yellow-300 shadow-lg cursor-pointer' }}">
                        <div class="p-4 space-y-2">
                            <div
                                class="text-2xl font-black text-center {{ $disabled ? 'text-gray-500' : 'text-secondary-300' }}">
                                {{ $reference['noKunjungan'] ?? ($reference['noRujukan'] ?? '-') }}
                            </div>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                                <div>
                                    <div
                                        class="font-bold uppercase tracking-widest {{ $disabled ? 'text-gray-600' : 'text-secondary-300' }}">
                                        Poli Tujuan
                                    </div>
                                    <div
                                        class="{{ $disabled ? 'text-gray-400' : 'text-white font-semibold drop-shadow-sm' }}">
                                        {{ $reference['poliRujukan']['nama'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div
                                        class="font-bold uppercase tracking-widest {{ $disabled ? 'text-gray-600' : 'text-secondary-300' }}">
                                        PPK (FKTP)
                                    </div>
                                    <div
                                        class="{{ $disabled ? 'text-gray-400' : 'text-white font-semibold drop-shadow-sm' }}">
                                        {{ $reference['provPerujuk']['kode'] ? $reference['provPerujuk']['kode'] . '-' . $reference['provPerujuk']['nama'] : '-' }}
                                    </div>
                                </div>
                                <div>
                                    <div
                                        class="font-bold uppercase tracking-widest {{ $disabled ? 'text-gray-600' : 'text-secondary-300' }}">
                                        Diagnosa
                                    </div>
                                    <div
                                        class="{{ $disabled ? 'text-gray-400' : 'text-white font-semibold drop-shadow-sm' }}">
                                        {{ $reference['diagnosa']['nama'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div
                                        class="font-bold uppercase tracking-widest {{ $disabled ? 'text-gray-600' : 'text-secondary-300' }}">
                                        Tanggal Rujukan
                                    </div>
                                    <div
                                        class="{{ $disabled ? 'text-gray-400' : 'text-white font-semibold drop-shadow-sm' }}">
                                        {{ $reference['tglKunjungan'] ? \Carbon\Carbon::parse($reference['tglKunjungan'])->format('d F Y') : '-' }}
                                    </div>
                                </div>
                                @if ($reference['expiredAt'])
                                    <div class="font-bold {{ $disabled ? 'text-gray-600' : 'text-secondary-300' }}">
                                        Berlaku s/d {{ $reference['expiredAt'] }}
                                    </div>
                                @endif
                                @if ($reference['hasUsed'])
                                    <div
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-500/30 text-red-300 text-xs font-semibold">
                                        <flux:icon name="x-circle" class="size-3" /> SEP sudah diterbitkan
                                    </div>
                                @endif
                                @if ($reference['isExpired'])
                                    <div
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-yellow-500/30 text-yellow-300 text-xs font-semibold">
                                        <flux:icon name="exclamation-triangle" class="size-3" /> Kadaluarsa
                                    </div>
                                @endif
                            </div>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Rujukan RS --}}
    @if (!empty($listOfReferences['rs']))
        <div class="rounded-2xl overflow-hidden shadow-xl">
            <div class="flex items-center gap-3 px-5 py-3 bg-primary-700">
                <flux:icon name="building-office-2" class="size-5 text-secondary-300" />
                <span class="text-sm font-bold text-white uppercase tracking-widest">Rujukan Rumah Sakit</span>
            </div>
            <div class="flex flex-col gap-3 p-4 bg-black/20">
                @foreach ($listOfReferences['rs'] as $reference)
                    @php
                        $disabled = $reference['isExpired'];
                        $jsonRef = htmlspecialchars(json_encode($reference), ENT_QUOTES, 'UTF-8');
                    @endphp
                    <button
                        @if (!$disabled) wire:loading.attr="disabled"
                            wire:target="selectReference"
                            x-on:click="$wire.selectReference('{{ $jsonRef }}', 'rs')" @endif
                        @disabled($disabled)
                        class="w-full text-left rounded-xl transition-all duration-200 active:scale-[0.99]
                            {{ $disabled
                                ? 'bg-gray-800 border border-gray-700 cursor-not-allowed'
                                : 'bg-gradient-to-br from-primary-700 to-primary-500 hover:to-yellow-300 shadow-lg cursor-pointer' }}">
                        <div class="p-4 space-y-2">
                            <div
                                class="text-2xl font-black text-center {{ $disabled ? 'text-gray-500' : 'text-secondary-300' }}">
                                {{ $reference['noKunjungan'] ?? ($reference['noRujukan'] ?? '-') }}
                            </div>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                                <div>
                                    <div
                                        class="font-bold uppercase tracking-widest {{ $disabled ? 'text-gray-600' : 'text-secondary-300' }}">
                                        Poli Tujuan
                                    </div>
                                    <div
                                        class="{{ $disabled ? 'text-gray-400' : 'text-white font-semibold drop-shadow-sm' }}">
                                        {{ $reference['poliRujukan']['nama'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div
                                        class="font-bold uppercase tracking-widest {{ $disabled ? 'text-gray-600' : 'text-secondary-300' }}">
                                        PPK (RS)
                                    </div>
                                    <div
                                        class="{{ $disabled ? 'text-gray-400' : 'text-white font-semibold drop-shadow-sm' }}">
                                        {{ $reference['provPerujuk']['kode'] ? $reference['provPerujuk']['kode'] . '-' . $reference['provPerujuk']['nama'] : '-' }}
                                    </div>
                                </div>
                                <div>
                                    <div
                                        class="font-bold uppercase tracking-widest {{ $disabled ? 'text-gray-600' : 'text-secondary-300' }}">
                                        Diagnosa
                                    </div>
                                    <div
                                        class="{{ $disabled ? 'text-gray-400' : 'text-white font-semibold drop-shadow-sm' }}">
                                        {{ $reference['diagnosa']['nama'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div
                                        class="font-bold uppercase tracking-widest {{ $disabled ? 'text-gray-600' : 'text-secondary-300' }}">
                                        Tanggal Rujukan
                                    </div>
                                    <div
                                        class="{{ $disabled ? 'text-gray-400' : 'text-white font-semibold drop-shadow-sm' }}">
                                        {{ $reference['tglKunjungan'] ? \Carbon\Carbon::parse($reference['tglKunjungan'])->format('d F Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                            @if ($reference['expiredAt'])
                                <div class="font-bold {{ $disabled ? 'text-gray-600' : 'text-secondary-300' }}">
                                    Berlaku s/d {{ $reference['expiredAt'] }}
                                </div>
                            @endif
                            @if ($reference['hasUsed'])
                                <div
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-500/30 text-red-300 text-xs font-semibold">
                                    <flux:icon name="x-circle" class="size-3" /> SEP sudah diterbitkan
                                </div>
                            @endif
                            @if ($reference['isExpired'])
                                <div
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-yellow-500/30 text-yellow-300 text-xs font-semibold">
                                    <flux:icon name="exclamation-triangle" class="size-3" /> Kadaluarsa
                                </div>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Jika tidak ada rujukan sama sekali --}}
    @if (empty($listOfReferences['fktp']) && empty($listOfReferences['rs']))
        <div class="rounded-2xl overflow-hidden shadow-xl">
            <div class="flex items-center gap-3 px-5 py-3 bg-yellow-700/80">
                <flux:icon name="exclamation-triangle" class="size-5 text-secondary-300" />
                <span class="text-sm font-bold text-white uppercase tracking-widest">Data Tidak Ditemukan</span>
            </div>
            <div class="p-5 bg-black/20 text-white/70 text-sm">
                Tidak ditemukan data rujukan untuk nomor peserta ini. Silakan hubungi petugas.
            </div>
        </div>
    @endif
</div>
