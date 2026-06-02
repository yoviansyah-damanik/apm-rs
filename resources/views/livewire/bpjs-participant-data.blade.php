<div>
    <table class="min-w-full overflow-hidden divide-y divide-gray-200 table-auto">
        <tbody class="text-sm bg-white divide-y divide-gray-100">
            <tr>
                <td class="px-4 py-1 font-semibold w-44">No Kartu</td>
                <td class="px-4 py-1">{{ $participantData['peserta']['noKartu'] }}</td>
            </tr>
            <tr>
                <td class="px-4 py-1 font-semibold w-44">NIK</td>
                <td class="px-4 py-1">{{ $participantData['peserta']['nik'] }}</td>
            </tr>
            <tr>
                <td class="px-4 py-1 font-semibold w-44">Nama</td>
                <td class="px-4 py-1">{{ $participantData['peserta']['nama'] }}</td>
            </tr>
            <tr>
                <td class="px-4 py-1 font-semibold w-44">Jenis Kelamin</td>
                <td class="px-4 py-1">
                    {{ $participantData['peserta']['sex'] == 'P' ? 'Perempuan' : 'Laki-laki' }}
                </td>
            </tr>
            <tr>
                <td class="px-4 py-1 font-semibold w-44">No MR</td>
                <td class="px-4 py-1">{{ $participantData['peserta']['mr']['noMR'] ?? '-' }}
                </td>
            </tr>
            <tr>
                <td class="px-4 py-1 font-semibold w-44">Telepon</td>
                <td class="px-4 py-1">
                    {{ $participantData['peserta']['mr']['noTelepon'] ?? '-' }}
                </td>
            </tr>
            <tr>
                <td class="px-4 py-1 font-semibold w-44">Tanggal Lahir</td>
                <td class="px-4 py-1">
                    {{ \Carbon\Carbon::parse($participantData['peserta']['tglLahir'])->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td class="px-4 py-1 font-semibold w-44">Tanggal Cetak Kartu</td>
                <td class="px-4 py-1">
                    {{ \Carbon\Carbon::parse($participantData['peserta']['tglCetakKartu'])->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td class="px-4 py-1 font-semibold w-44">Tanggal TMT</td>
                <td class="px-4 py-1">
                    {{ \Carbon\Carbon::parse($participantData['peserta']['tglTMT'])->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td class="px-4 py-1 font-semibold w-44">Tanggal TAT</td>
                <td class="px-4 py-1">
                    {{ \Carbon\Carbon::parse($participantData['peserta']['tglTAT'])->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td class="px-4 py-1 font-semibold w-44">Jenis Peserta</td>
                <td class="px-4 py-1">
                    {{ $participantData['peserta']['jenisPeserta']['keterangan'] }}
                </td>
            </tr>
            <tr>
                <td class="px-4 py-1 font-semibold w-44">Hak Kelas</td>
                <td class="px-4 py-1">
                    {{ $participantData['peserta']['hakKelas']['keterangan'] }}
                </td>
            </tr>
            <tr>
                <td class="px-4 py-1 font-semibold w-44">Provider Umum</td>
                <td class="px-4 py-1">
                    {{ $participantData['peserta']['provUmum']['nmProvider'] }}
                </td>
            </tr>
            <tr>
                <td class="px-4 py-1 font-semibold w-44">Umur</td>
                <td class="px-4 py-1">
                    {{ $participantData['peserta']['umur']['umurSekarang'] }}
                </td>
            </tr>
            <tr>
                <td class="px-4 py-1 font-semibold w-44">Status Peserta</td>
                <td @class([
                    'px-4 py-1 font-bold text-white text-center tracking-widest',
                    'bg-primary-700' =>
                        $participantData['peserta']['statusPeserta']['keterangan'] == 'AKTIF',
                    'bg-red-700' =>
                        $participantData['peserta']['statusPeserta']['keterangan'] != 'AKTIF',
                ])>
                    {{ $participantData['peserta']['statusPeserta']['keterangan'] }}
                </td>
            </tr>
        </tbody>
    </table>
</div>