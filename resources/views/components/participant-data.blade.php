@props([
    'participantData' => [],
    'mrNumber' => null,
    'phoneNumber' => null,
])

<div class="bg-white rounded-lg overflow-hidden">
    <div class="bg-primary-700 text-secondary-300 text-center py-2 px-4 font-bold tracking-widest">
        DATA PESERTA
    </div>
    <div class="p-4">
        <div class="[&>*:not(:last-child)]:mr-4 space-y-2 [&>*]:px-4 [&>*]:inline-block">
            <div>
                <div class="font-semibold text-sm">No. RM</div>
                <div>
                    {{ $participantData['peserta']['mr']['noMR'] != null ? $participantData['peserta']['mr']['noMR'] : $mrNumber }}
                </div>
            </div>
            <div>
                <div class="font-semibold text-sm">NIK</div>
                <div>{{ Magic::masking($participantData['peserta']['nik']) }}</div>
            </div>
            <div>
                <div class="font-semibold text-sm">No. Kartu BPJS</div>
                <div>{{ Magic::masking($participantData['peserta']['noKartu']) }}</div>
            </div>
            <div>
                <div class="font-semibold text-sm">Nama Pasien</div>
                <div>{{ $participantData['peserta']['nama'] }}</div>
            </div>
            <div>
                <div class="font-semibold text-sm">Tanggal Lahir</div>
                <div>{{ \Carbon\Carbon::parse($participantData['peserta']['tglLahir'])->translatedFormat('d F Y') }}
                </div>
            </div>
            <div>
                <div class="font-semibold text-sm">Jenis Kelamin</div>
                <div>{{ $participantData['peserta']['sex'] == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
            </div>
            <div>
                <div class="font-semibold text-sm">No. Telp</div>
                <div>
                    {{ $participantData['peserta']['mr']['noTelepon'] ? $participantData['peserta']['mr']['noTelepon'] : $phoneNumber }}
                </div>
            </div>
            <div>
                <div class="font-semibold text-sm">Hak Kelas</div>
                <div>{{ $participantData['peserta']['hakKelas']['keterangan'] }}</div>
            </div>
            <div>
                <div class="font-semibold text-sm">Jenis Peserta</div>
                <div>{{ $participantData['peserta']['jenisPeserta']['keterangan'] }}</div>
            </div>
            <div>
                <div class="font-semibold text-sm">FKTP</div>
                <div>
                    {{ $participantData['peserta']['provUmum']['kdProvider'] . ' - ' . $participantData['peserta']['provUmum']['nmProvider'] }}
                </div>
            </div>
            <div>
                <div class="font-semibold text-sm">Status Peserta</div>
                <div>{{ $participantData['peserta']['statusPeserta']['keterangan'] }}</div>
            </div>
        </div>
    </div>
</div>
