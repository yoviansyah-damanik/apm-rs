<div class="space-y-6">
    {{-- Info Jenis Kunjungan --}}
    @if ($purposeOfVisit)
        <div class="flex items-center gap-3 px-5 py-3 rounded-xl bg-black/30 border border-secondary-300/20">
            <flux:icon name="information-circle" class="size-5 text-secondary-300 shrink-0" />
            <span class="text-sm text-white/80">
                Menampilkan surat kontrol untuk jenis kunjungan:
                <span class="font-bold text-white">
                    @if ($purposeOfVisit->name === 'Kontrol')
                        Rawat Jalan
                    @elseif ($purposeOfVisit->name === 'KontrolPostRanap')
                        Rawat Inap (Post Ranap)
                    @else
                        {{ $purposeOfVisit->value }}
                    @endif
                </span>
            </span>
        </div>
    @endif

    {{-- List Surat Kontrol --}}
    @if (!empty($listOfControlLetters))
        <div class="rounded-2xl overflow-hidden shadow-xl">
            <div class="flex items-center gap-3 px-5 py-3 bg-primary-700">
                <flux:icon name="clipboard-document-check" class="size-5 text-secondary-300" />
                <span class="text-sm font-bold text-white uppercase tracking-widest">Daftar Surat Kontrol</span>
            </div>
            <div class="flex flex-col gap-3 p-4 bg-black/20">
                @foreach ($listOfControlLetters as $controlLetter)
                    @php
                        $hasSep = isset($controlLetter['terbitSEP']) && $controlLetter['terbitSEP'] === 'Sudah';
                        $tglRencana = \Carbon\Carbon::parse($controlLetter['tglRencanaKontrol']);
                        $isFuture = $tglRencana->isAfter(\Carbon\Carbon::today());
                        $isDisabled = $hasSep || $isFuture;
                        $jsonCl = htmlspecialchars(json_encode($controlLetter), ENT_QUOTES, 'UTF-8');
                    @endphp
                    <button
                        @if (!$isDisabled) wire:loading.attr="disabled"
                            wire:target="selectControlLetter"
                            x-on:click="$wire.selectControlLetter('{{ $jsonCl }}')" @endif
                        @disabled($isDisabled)
                        class="w-full text-left rounded-xl transition-all duration-200 active:scale-[0.99]
                            {{ $isDisabled
                                ? 'bg-gray-800 border border-gray-700 cursor-not-allowed pointer-events-none'
                                : 'bg-gradient-to-br from-primary-700 to-primary-500 hover:to-yellow-300 shadow-lg cursor-pointer' }}">
                        <div class="p-4 space-y-2">
                            <div class="text-lg font-black {{ $isDisabled ? 'text-gray-500' : 'text-secondary-300' }}">
                                {{ $controlLetter['noSuratKontrol'] ?? '-' }}
                            </div>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                <div>
                                    <div
                                        class="text-xs font-bold uppercase tracking-widest {{ $isDisabled ? 'text-gray-600' : 'text-primary-950' }}">
                                        Poli</div>
                                    <div
                                        class="{{ $isDisabled ? 'text-gray-400' : 'text-white font-semibold drop-shadow-sm' }}">
                                        {{ $controlLetter['namaPoliTujuan'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div
                                        class="text-xs font-bold uppercase tracking-widest {{ $isDisabled ? 'text-gray-600' : 'text-primary-950' }}">
                                        Dokter</div>
                                    <div
                                        class="{{ $isDisabled ? 'text-gray-400' : 'text-white font-semibold drop-shadow-sm' }}">
                                        {{ $controlLetter['namaDokter'] ?? '-' }}</div>
                                </div>
                                <div class="col-span-2">
                                    <div
                                        class="text-xs font-bold uppercase tracking-widest {{ $isDisabled ? 'text-gray-600' : 'text-primary-950' }}">
                                        Rencana Kontrol</div>
                                    <div
                                        class="{{ $isDisabled ? 'text-gray-400' : 'text-white font-semibold drop-shadow-sm' }}">
                                        {{ $tglRencana->format('d-m-Y') }}</div>
                                </div>
                            </div>
                            @if ($hasSep)
                                <div
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-500/30 text-red-300 text-xs font-semibold">
                                    <flux:icon name="x-circle" class="size-3" /> Sudah menerbitkan SEP
                                </div>
                            @elseif ($isFuture)
                                <div
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-yellow-500/30 text-yellow-300 text-xs font-semibold">
                                    <flux:icon name="clock" class="size-3" /> Tanggal kontrol belum tiba
                                </div>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Jika tidak ada surat kontrol --}}
    @if (empty($listOfControlLetters))
        <div class="rounded-2xl overflow-hidden shadow-xl">
            <div class="flex items-center gap-3 px-5 py-3 bg-yellow-700/80">
                <flux:icon name="exclamation-triangle" class="size-5 text-secondary-300" />
                <span class="text-sm font-bold text-white uppercase tracking-widest">Data Tidak Ditemukan</span>
            </div>
            <div class="p-5 bg-black/20 text-white/70 text-sm">
                Tidak ditemukan data surat kontrol untuk nomor peserta ini. Silakan hubungi petugas.
            </div>
        </div>
    @endif
</div>
