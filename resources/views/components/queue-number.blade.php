@props([
    'type' => '',
    'queueNumber' => 0,
    'calledQueueNumber' => 0,
    'date' => now()->format('d/m/Y'),
    'data' => [],
    'notes' => [],
    'queueNumberSize' => 'text-9xl',
    'recipes' => [],
])

<div {{ $attributes->merge(['class' => 'w-full bg-white print:bg-white rounded-2xl print:rounded-none shadow-xl print:!shadow-none print:p-0 print:px-4 overflow-hidden']) }}
    id="queuePrint">
    <div class="hidden print:block border-b-2 print:border-black mb-4">
        <div class="text-center text-2xl font-bold leading-7">{{ Setting::get('hospitalName') }}</div>
        <div class="text-center print:text-base text-xs mb-4 leading-4 mt-1">{{ Setting::get('hospitalAddress') }}</div>
    </div>
    @if ($type == 'loket')
        <div class="text-center mb-4 print:mb-6 hidden print:block">
            <h2 class="text-2xl print:text-xl font-bold uppercase tracking-wider">Bukti Register Pendaftaran</h2>
        </div>

        {{-- Loket: Tampilan Layar --}}
        <div class="print:hidden flex items-stretch overflow-hidden">
            {{-- Kiri --}}
            <div
                class="relative bg-gradient-to-b from-primary-600 to-primary-800 flex flex-col items-center justify-center px-8 py-5 shrink-0 overflow-hidden">
                <div class="absolute -bottom-6 -left-6 w-28 h-28 rounded-full bg-white/5"></div>
                <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-white/5"></div>
                <div class="relative z-10 text-center">
                    <div class="text-secondary-300/70 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Antrean
                    </div>
                    <div @class([
                        'font-black font-mono text-white leading-none drop-shadow-lg',
                        $queueNumberSize,
                    ])>{{ $queueNumber }}</div>
                    <div class="mt-2 h-0.5 w-12 bg-secondary-300/40 mx-auto rounded-full"></div>
                </div>
            </div>
            {{-- Kanan --}}
            <div class="flex-1 flex flex-col justify-center gap-2 px-5 py-4 bg-white border-l-4 border-primary-600">
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1 bg-primary-100 text-primary-700 text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Terdaftar
                    </span>
                </div>
                <div class="font-bold text-gray-800 text-base leading-tight">Loket Pendaftaran</div>
                <div class="text-sm text-gray-400">{{ $date }}</div>
                <div class="text-xs text-amber-600 font-semibold">Siapkan berkas dan tunggu dipanggil petugas.</div>
            </div>
        </div>

        <div class="hidden print:block">
            <div class="text-center text-lg">Loket Pendaftaran</div>
            <div class="text-center font-black font-mono" style="font-size: 5rem; line-height: 1;">{{ $queueNumber }}
            </div>
            <div class="text-center text-base text-gray-500 mt-1">{{ $date }}</div>
            <div class="text-center text-base mt-4">
                Terima kasih telah mempercayakan pelayanan kesehatan Anda kepada kami.
                Semoga lekas sembuh dan sehat selalu.
            </div>
            @if (count($notes) > 0)
                <div class="mt-5 text-xs print:text-base">
                    <flux:separator class="my-1" />
                    <ol class="text-gray-500 print:text-black list-decimal px-6">
                        @foreach ($notes as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ol>
                </div>
            @else
                <div class="mt-5 text-xs print:text-base">
                    <flux:separator class="my-1" />
                    <ol class="text-gray-500 print:text-black list-decimal px-6">
                        <li>Silahkan persiapkan berkas yang diperlukan.</li>
                        <li>Serahkan nomor antrean ini pada saat dipanggil.</li>
                        <li>Silahkan tunggu giliran Anda di Loket Pendaftaran.</li>
                        <li>Anda akan dipanggil kembali sebanyak 1x setelah melewati 3 antrean. Jika Anda tidak merespon
                            pada antrean selanjutnya, Anda akan dianggap membatalkan antrean.</li>
                    </ol>
                </div>
            @endif
        </div>
    @elseif($type == 'poli')
        {{-- Poli: Tampilan Layar --}}
        <div class="print:hidden flex items-stretch overflow-hidden">
            {{-- Kiri: Nomor Antrean --}}
            <div
                class="relative bg-gradient-to-b from-primary-600 to-primary-800 flex flex-col items-center justify-center px-8 py-5 shrink-0 overflow-hidden">
                <div class="absolute -bottom-6 -left-6 w-28 h-28 rounded-full bg-white/5"></div>
                <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-white/5"></div>
                <div class="relative z-10 text-center">
                    <div class="text-secondary-300 text-base font-bold uppercase tracking-[0.2em] mb-1">Antrean
                        Poliklinik</div>
                    <div @class([
                        'font-black font-mono text-white leading-none drop-shadow-lg',
                        $queueNumberSize,
                    ])>{{ $queueNumber }}</div>
                    @if (!empty($data['nm_poli']))
                        <div class="mt-2 h-0.5 w-12 bg-secondary-300/40 mx-auto rounded-full"></div>
                        <div class="mt-2 text-secondary-300 font-semibold text-base text-center leading-snug w-full">
                            {{ $data['nm_poli'] }}</div>
                    @endif
                </div>
            </div>

            {{-- Kanan: Detail --}}
            <div class="flex-1 flex flex-col justify-between px-5 py-4 bg-white border-l-4 border-primary-600">
                {{-- Nama + badge --}}
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase tracking-widest mb-0.5">Pasien</div>
                        <div class="font-bold text-gray-800 text-base leading-tight">{{ $data['nm_pasien'] ?? '-' }}
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">No. RM: <span
                                class="font-semibold text-gray-600">{{ $data['no_rkm_medis'] ?? '-' }}</span></div>
                    </div>
                    <span
                        class="inline-flex items-center gap-1 bg-primary-100 text-primary-700 text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full shrink-0">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Antrean Poli
                    </span>
                </div>
                {{-- Detail baris --}}
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-400 text-xs shrink-0">Dokter</span>
                        <span
                            class="font-semibold text-gray-700 text-xs text-right leading-tight">{{ $data['nm_dokter'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-400 text-xs shrink-0">Est. Dilayani</span>
                        <span class="font-bold text-primary-700 text-xs">{{ $data['estimasi_dilayani'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-400 text-xs shrink-0">Cara Bayar</span>
                        <span class="font-semibold text-gray-700 text-xs">{{ $data['png_jawab'] ?? '-' }}</span>
                    </div>
                </div>
                {{-- Footer hint --}}
                <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-xs text-amber-600">Menuju
                        <strong>{{ $data['nm_poli'] ?? 'poli tujuan' }}</strong> dan tunggu dipanggil.</span>
                </div>
            </div>
        </div>

        {{-- Model Cetak Poli --}}
        <div class="w-full text-center print:text-left hidden print:block">
            <div class="text-center mb-4 print:mb-6">
                <h2 class="text-2xl print:text-xl font-bold uppercase tracking-wider">Bukti Register Poliklinik</h2>
            </div>

            {{-- Main Content Grid --}}
            <div class="grid print:grid-cols-1 grid-cols-2 gap-x-8 gap-y-1 text-left mb-6">
                {{-- Kolom Kiri --}}
                <div class="space-y-2">
                    <div class="flex">
                        <span class="font-semibold w-32">Nama</span>
                        <span class="mr-2">:</span>
                        <span class="flex-1">{{ $data['nm_pasien'] ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="font-semibold w-32">No.RM</span>
                        <span class="mr-2">:</span>
                        <span class="flex-1">{{ $data['no_rkm_medis'] ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="font-semibold w-32">Poli</span>
                        <span class="mr-2">:</span>
                        <span class="flex-1">{{ $data['nm_poli'] ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="font-semibold w-32">Tanggal</span>
                        <span class="mr-2">:</span>
                        <span class="flex-1">{{ $data['tgl_registrasi'] ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="font-semibold w-32">Jenis Kelamin</span>
                        <span class="mr-2">:</span>
                        <span
                            class="flex-1">{{ $data['jk'] == 'L' ? 'Laki-Laki' : ($data['jk'] == 'P' ? 'Perempuan' : '-') }}</span>
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="space-y-2">
                    <div class="flex">
                        <span class="font-semibold w-32">No.Antrean</span>
                        <span class="mr-2">:</span>
                        <span class="flex-1 font-bold text-2xl print:text-3xl">{{ $queueNumber }}</span>
                    </div>
                    <div class="flex">
                        <span class="font-semibold w-32">Dokter</span>
                        <span class="mr-2">:</span>
                        <span class="flex-1">{{ $data['nm_dokter'] ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="font-semibold w-32">Est. Dilayani</span>
                        <span class="mr-2">:</span>
                        <span class="flex-1">{{ $data['estimasi_dilayani'] ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="font-semibold w-32">Cara Bayar</span>
                        <span class="mr-2">:</span>
                        <span class="flex-1 font-bold">{{ $data['png_jawab'] ?? '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- Barcode Kode Booking --}}
            @if (!empty($data['no_rawat']))
                @php
                    $barcodeGenerator = new \Picqer\Barcode\BarcodeGeneratorSVG();
                    $barcodeSvg = $barcodeGenerator->getBarcode(
                        $data['no_rawat'],
                        \Picqer\Barcode\BarcodeGenerator::TYPE_CODE_128,
                        2,
                        55,
                    );
                @endphp
                <div class="flex justify-center my-3">
                    <div
                        class="inline-flex flex-col items-center w-full border border-gray-300 rounded px-3 pt-2 pb-1">
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-600 mb-1">Kode
                            Booking</span>
                        {!! $barcodeSvg !!}
                        <span class="text-xs text-gray-500 mt-0.5">{{ $data['no_rawat'] }}</span>
                    </div>
                </div>
            @endif

            {{-- Footer Messages --}}
            <div class="text-center text-base mt-4">
                Terima kasih telah mempercayakan pelayanan kesehatan Anda kepada kami.
                Semoga lekas sembuh dan sehat selalu.
            </div>

            @if (count($notes) > 0)
                <div class="mt-5 text-xs print:text-base">
                    <flux:separator class="my-1" />
                    <ol class="text-gray-500 print:text-black list-decimal px-6">
                        @foreach ($notes as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ol>
                </div>
            @else
                <div class="mt-5 text-xs print:text-base">
                    <flux:separator class="my-1" />
                    <ol class="text-gray-500 print:text-black list-decimal px-6">
                        <li>Serahkan nomor antrean ini pada saat dipanggil.</li>
                        <li>Silahkan tunggu giliran Anda di Poliklinik tujuan.</li>
                        <li>Anda akan dipanggil kembali sebanyak 1x setelah melewati 3 antrean. Jika Anda tidak merespon
                            pada antrean selanjutnya, Anda akan dianggap membatalkan antrean.</li>
                    </ol>
                </div>
            @endif
        </div>
    @elseif($type == 'farmasi')
        @if (!empty($recipes))
            {{-- Farmasi Multi-Resep: Tampilan Layar --}}
            <div class="print:hidden flex items-stretch overflow-hidden">
                {{-- Kiri: Semua Nomor Antrean --}}
                <div
                    class="relative bg-gradient-to-b from-blue-600 to-blue-800 flex flex-col items-center justify-center px-8 py-6 shrink-0 overflow-hidden">
                    <div class="absolute -bottom-6 -left-6 w-28 h-28 rounded-full bg-white/5"></div>
                    <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-white/5"></div>
                    <div class="relative z-10 text-center">
                        <div class="text-secondary-300/80 text-[10px] font-bold uppercase tracking-[0.2em] mb-2">
                            Antrean
                            Farmasi</div>
                        @foreach ($recipes as $recipe)
                            <div
                                class="{{ count($recipes) == 1 ? 'text-9xl' : 'text-5xl' }} font-black font-mono text-white leading-none drop-shadow-lg">
                                {{ $recipe['queue']['no_antrian'] ?? '-' }}
                            </div>
                            @if (!$loop->last)
                                <div class="my-2 h-px w-16 bg-white/20 mx-auto"></div>
                            @endif
                        @endforeach
                        <div class="mt-3 h-0.5 w-12 bg-secondary-300/40 mx-auto rounded-full"></div>
                    </div>
                </div>

                {{-- Kanan: Detail --}}
                <div class="flex-1 flex flex-col justify-between px-5 py-4 bg-white border-l-4 border-blue-600">
                    {{-- Nama + badge --}}
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase tracking-widest mb-0.5">Pasien</div>
                            <div class="font-bold text-gray-800 text-base leading-tight">
                                {{ $data['patient']['nm_pasien'] ?? '-' }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">No. RM: <span
                                    class="font-semibold text-gray-600">{{ $data['patient']['no_rkm_medis'] ?? '-' }}</span>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full shrink-0">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Antrean Farmasi
                        </span>
                    </div>
                    {{-- Daftar Resep --}}
                    <div class="space-y-2">
                        @foreach ($recipes as $recipe)
                            <div class="rounded-lg bg-blue-50 border border-blue-100 px-3 py-2">
                                <div class="flex justify-between items-center gap-2">
                                    <div>
                                        <div class="text-xs text-gray-400">No. Resep</div>
                                        <div class="font-semibold text-gray-800 text-sm">
                                            {{ $recipe['no_resep'] ?? '-' }}</div>
                                    </div>
                                    <span
                                        class="text-xs text-blue-600 font-semibold bg-white border border-blue-200 rounded-full px-2 py-0.5 shrink-0">
                                        {{ ($recipe['jenis_resep'] ?? '-') . ' / ' . ($recipe['tipe_resep'] ?? '-') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{-- Footer --}}
                    <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs text-amber-600">Menuju <strong>ruang tunggu Farmasi</strong> dan
                                tunggu dipanggil.</span>
                        </div>
                        <span
                            class="text-xs text-gray-400 shrink-0">{{ $data['patient']['paytype']['png_jawab'] ?? '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- Farmasi Multi-Resep: Model Cetak --}}
            <div class="w-full print:text-left hidden print:block">
                <div class="text-center mb-4">
                    <h2 class="text-xl font-bold uppercase tracking-wider">Antrean Farmasi</h2>
                </div>
                <div class="space-y-1 text-left mb-4">
                    <div class="flex">
                        <span class="font-semibold w-32">Nama</span>
                        <span class="mr-2">:</span>
                        <span>{{ $data['patient']['nm_pasien'] ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="font-semibold w-32">No. RM</span>
                        <span class="mr-2">:</span>
                        <span>{{ $data['patient']['no_rkm_medis'] ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="font-semibold w-32">Cara Bayar</span>
                        <span class="mr-2">:</span>
                        <span>{{ $data['patient']['paytype']['png_jawab'] ?? '-' }}</span>
                    </div>
                    <div class="flex">
                        <span class="font-semibold w-32">Tanggal</span>
                        <span class="mr-2">:</span>
                        <span>{{ $data['tgl_registrasi'] ?? '-' }}</span>
                    </div>
                </div>
                <div class="border border-gray-400 rounded overflow-hidden mb-4">
                    <div class="bg-gray-100 px-3 py-1.5 font-bold text-sm border-b border-gray-400">Daftar Nomor
                        Antrean</div>
                    @foreach ($recipes as $recipe)
                        <div class="flex items-center gap-4 px-3 py-2 border-b border-gray-200 last:border-b-0">
                            <div class="font-black font-mono text-3xl w-28 shrink-0">
                                {{ $recipe['queue']['no_antrian'] ?? '-' }}
                            </div>
                            <div class="flex-1 text-sm space-y-0.5">
                                <div><span class="font-semibold">No. Resep:</span> {{ $recipe['no_resep'] ?? '-' }}
                                </div>
                                <div><span class="font-semibold">Jenis:</span>
                                    {{ ($recipe['jenis_resep'] ?? '-') . ' / ' . ($recipe['tipe_resep'] ?? '-') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if (!empty($data['no_rawat']))
                    @php
                        $barcodeGeneratorFarmasi = new \Picqer\Barcode\BarcodeGeneratorSVG();
                        $barcodeSvgFarmasi = $barcodeGeneratorFarmasi->getBarcode(
                            $data['no_rawat'],
                            \Picqer\Barcode\BarcodeGenerator::TYPE_CODE_128,
                            2,
                            55,
                        );
                    @endphp
                    <div class="flex justify-center my-3">
                        <div
                            class="inline-flex flex-col w-full items-center border border-gray-300 rounded px-3 pt-2 pb-1">
                            <span class="text-xs font-bold uppercase tracking-widest text-gray-600 mb-1">Kode
                                Booking</span>
                            {!! $barcodeSvgFarmasi !!}
                            <span class="text-xs text-gray-500 mt-0.5">{{ $data['no_rawat'] }}</span>
                        </div>
                    </div>
                @endif
                <div class="text-center text-base mt-4">
                    Terima kasih telah mempercayakan pelayanan kesehatan Anda kepada kami.
                    Semoga lekas sembuh dan sehat selalu.
                </div>
                <div class="mt-4">
                    <ol class="text-black list-decimal px-6 text-sm">
                        <li>Serahkan nomor antrean ini pada saat dipanggil di Farmasi.</li>
                        <li>Silahkan tunggu giliran Anda di ruang tunggu Farmasi.</li>
                        <li>Apabila nomor antrean Anda terlewat, tunjukkan nomor antrean ini kepada Petugas.</li>
                    </ol>
                </div>
            </div>
        @else
            {{-- Farmasi Single: Tampilan Layar --}}
            <div class="print:hidden flex items-stretch overflow-hidden">
                {{-- Kiri: Nomor Antrean --}}
                <div
                    class="relative bg-gradient-to-b from-blue-600 to-blue-800 flex flex-col items-center justify-center px-8 py-5 shrink-0 overflow-hidden">
                    <div class="absolute -bottom-6 -left-6 w-28 h-28 rounded-full bg-white/5"></div>
                    <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-white/5"></div>
                    <div class="relative z-10 text-center">
                        <div class="text-white/60 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Farmasi</div>
                        <div @class([
                            'font-black font-mono text-white leading-none drop-shadow-lg',
                            $queueNumberSize,
                        ])>{{ $queueNumber }}</div>
                        <div class="mt-2 h-0.5 w-12 bg-white/30 mx-auto rounded-full"></div>
                    </div>
                </div>

                {{-- Kanan: Detail --}}
                <div class="flex-1 flex flex-col justify-between px-5 py-4 bg-white border-l-4 border-blue-600">
                    {{-- Nama + badge --}}
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase tracking-widest mb-0.5">Pasien</div>
                            <div class="font-bold text-gray-800 text-base leading-tight">
                                {{ $data['patient']['nm_pasien'] ?? '-' }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">No. RM: <span
                                    class="font-semibold text-gray-600">{{ $data['patient']['no_rkm_medis'] ?? '-' }}</span>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center gap-1 bg-primary-100 text-primary-700 text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full shrink-0">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Antrean Farmasi
                        </span>
                    </div>
                    {{-- Detail baris --}}
                    <div class="space-y-1.5">
                        <div class="flex justify-between gap-2">
                            <span class="text-gray-400 text-xs shrink-0">No. Resep</span>
                            <span class="font-semibold text-gray-700 text-xs">{{ $data['no_resep'] ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between gap-2">
                            <span class="text-gray-400 text-xs shrink-0">Jenis / Tipe</span>
                            <span
                                class="font-semibold text-gray-700 text-xs text-right">{{ ($data['jenis_resep'] ?? '-') . ' / ' . ($data['tipe_resep'] ?? '-') }}</span>
                        </div>
                        <div class="flex justify-between gap-2">
                            <span class="text-gray-400 text-xs shrink-0">Cara Bayar</span>
                            <span
                                class="font-semibold text-gray-700 text-xs">{{ $data['patient']['paytype']['png_jawab'] ?? '-' }}</span>
                        </div>
                    </div>
                    {{-- Footer hint --}}
                    <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs text-amber-600">Menuju <strong>ruang tunggu Farmasi</strong> dan
                            tunggu dipanggil.</span>
                    </div>
                </div>
            </div>

            {{-- Farmasi Single: Model Cetak --}}
            <div class="w-full text-center print:text-left print:block hidden">
                <div class="text-center mb-4 print:mb-6">
                    <h2 class="text-2xl print:text-xl font-bold uppercase tracking-wider">Antrean Farmasi</h2>
                </div>
                <div class="grid print:grid-cols-1 grid-cols-2 gap-x-8 gap-y-1 text-left mb-6">
                    <div class="space-y-2">
                        <div class="flex">
                            <span class="font-semibold w-32">Tanggal</span>
                            <span class="mr-2">:</span>
                            <span class="flex-1">{{ $data['tgl_registrasi'] ?? '-' }}</span>
                        </div>
                        <div class="flex">
                            <span class="font-semibold w-32">No.Resep</span>
                            <span class="mr-2">:</span>
                            <span class="flex-1">{{ $data['no_resep'] ?? '-' }}</span>
                        </div>
                        <div class="flex">
                            <span class="font-semibold w-32">Nama</span>
                            <span class="mr-2">:</span>
                            <span class="flex-1">{{ $data['patient']['nm_pasien'] ?? '-' }}</span>
                        </div>
                        <div class="flex">
                            <span class="font-semibold w-32">No.RM</span>
                            <span class="mr-2">:</span>
                            <span class="flex-1">{{ $data['patient']['no_rkm_medis'] ?? '-' }}</span>
                        </div>
                        <div class="flex">
                            <span class="font-semibold w-32">Jenis Kelamin</span>
                            <span class="mr-2">:</span>
                            <span
                                class="flex-1">{{ $data['patient']['jk'] == 'L' ? 'Laki-Laki' : ($data['patient']['jk'] == 'P' ? 'Perempuan' : '-') }}</span>
                        </div>
                        <div class="flex">
                            <span class="font-semibold w-32">No.Antrean</span>
                            <span class="mr-2">:</span>
                            <span class="flex-1 font-bold text-2xl print:text-3xl">{{ $queueNumber }}</span>
                        </div>
                        <div class="flex">
                            <span class="font-semibold w-32">Jenis Resep</span>
                            <span class="mr-2">:</span>
                            <span
                                class="flex-1 font-bold">{{ $data['jenis_resep'] . ' / ' . $data['tipe_resep'] }}</span>
                        </div>
                        <div class="flex">
                            <span class="font-semibold w-32">Cara Bayar</span>
                            <span class="mr-2">:</span>
                            <span class="flex-1 font-bold">{{ $data['patient']['paytype']['png_jawab'] }}</span>
                        </div>
                    </div>
                </div>
                @if (!empty($data['no_rawat']))
                    @php
                        $barcodeGeneratorFarmasiSingle = new \Picqer\Barcode\BarcodeGeneratorSVG();
                        $barcodeSvgFarmasiSingle = $barcodeGeneratorFarmasiSingle->getBarcode(
                            $data['no_rawat'],
                            \Picqer\Barcode\BarcodeGenerator::TYPE_CODE_128,
                            2,
                            55,
                        );
                    @endphp
                    <div class="flex justify-center my-3">
                        <div
                            class="inline-flex flex-col w-full items-center border border-gray-300 rounded px-3 pt-2 pb-1">
                            <span class="text-xs font-bold uppercase tracking-widest text-gray-600 mb-1">Kode
                                Booking</span>
                            {!! $barcodeSvgFarmasiSingle !!}
                            <span class="text-xs text-gray-500 mt-0.5">{{ $data['no_rawat'] }}</span>
                        </div>
                    </div>
                @endif
                <div class="text-center text-base mt-4">
                    Terima kasih telah mempercayakan pelayanan kesehatan Anda kepada kami.
                    Semoga lekas sembuh dan sehat selalu.
                </div>
                @if (count($notes) > 0)
                    <div class="mt-5 text-xs print:text-base">
                        <flux:separator class="my-1" />
                        <ol class="text-gray-500 print:text-black list-decimal px-6">
                            @foreach ($notes as $note)
                                <li>{{ $note }}</li>
                            @endforeach
                        </ol>
                    </div>
                @else
                    <div class="mt-5 text-xs print:text-base">
                        <flux:separator class="my-1" />
                        <ol class="text-gray-500 print:text-black list-decimal px-6">
                            <li>Serahkan nomor antrean ini pada saat dipanggil di Farmasi.</li>
                            <li>Silahkan tunggu giliran Anda di ruang tunggu Farmasi.</li>
                            <li>Apabila nomor antrean Anda terlewat, Anda dapat menunjukkan nomor antrean ini kepada
                                Petugas untuk selanjutnya dipanggil.</li>
                        </ol>
                    </div>
                @endif
            </div>
        @endif
    @endif
</div>
