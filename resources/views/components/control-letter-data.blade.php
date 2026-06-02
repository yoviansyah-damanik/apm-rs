@props([
    'listOfControlLetters' => [],
])

<div class="bg-white rounded-lg">
    <div class="bg-primary-700 text-secondary-300 text-center py-2 px-4 font-bold tracking-widest">
        SURAT KONTROL
    </div>
    <div class="py-4">
        @if (!empty($listOfControlLetters))
            <table class="table-fixed w-full">
                <thead class="border-b-2 border-primary-700 [*>]:whitespace-nowrap [*>]:py-2 [*>]:px-3">
                    <th>No Surat</th>
                    <th>Tanggal Rencana</th>
                    <th>Jenis Kontrol</th>
                    <th>Jenis Pelayanan</th>
                    <th>Poli Asal</th>
                    <th>Poli Tujuan</th>
                    <th>Nama Dokter</th>
                    <th>Ket</th>
                </thead>
                <tbody>
                    @foreach ($listOfControlLetters as $controlLetter)
                        <tr @class([
                            $controlLetter['terbitSEP'] == 'Sudah' ? 'bg-red-200' : '',
                            'border-b-1',
                        ])>
                            <td class="px-2 align-top py-2 text-center">
                                {{ $controlLetter['noSuratKontrol'] }}
                            </td>
                            <td class="px-2 align-top py-2 text-center">
                                {{ $controlLetter['tglRencanaKontrol'] }}
                            </td>
                            <td class="px-2 align-top py-2 text-center">
                                {{ $controlLetter['jnsKontrol'] == '1' ? 'PRI' : 'SKU' }}
                            </td>
                            <td class="px-2 align-top py-2 text-center">
                                {{ $controlLetter['jnsPelayanan'] }}
                            </td>
                            <td class="px-2 align-top py-2 text-center">
                                {{ $controlLetter['namaPoliAsal'] }}
                            </td>
                            <td class="px-2 align-top py-2 text-center">
                                {{ $controlLetter['namaPoliTujuan'] }}
                            </td>
                            <td class="px-2 align-top py-2 text-center">
                                {{ $controlLetter['namaDokter'] }}
                            </td>
                            <td class="px-2 align-top py-2 text-center">
                                {{ $controlLetter['terbitSEP'] == 'Sudah' ? 'Sudah Terbit SEP' : 'Belum Terbit SEP' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-center">
                Tidak ada surat kontrol ditemukan
            </div>
        @endif
    </div>
</div>
