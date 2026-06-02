@props([
    'listOfReferences' => [],
])

<div class="bg-white rounded-lg">
    <div class="bg-primary-700 text-secondary-300 text-center py-2 px-4 font-bold tracking-widest">
        RUJUKAN
    </div>
    <div class="py-4">
        @if (!empty($listOfReferences['fktp']) || !empty($listOfReferences['rs']))
            <table class="table-auto w-full">
                <thead class="border-b-2 border-primary-700 [*>]:whitespace-nowrap [*>]:py-2 [*>]:px-3">
                    <th>No Rujukan</th>
                    <th>Tanggal Rujukan</th>
                    <th>Poli Tujuan</th>
                    <th>PPK Rujukan</th>
                    <th>Diagnosa</th>
                    <th>Rujukan Habis</th>
                    <th>Ket</th>
                </thead>
                <tbody>
                    @if (!empty($listOfReferences['rs']))
                        @foreach ($listOfReferences['rs'] as $reference)
                            <tr @class([$reference['isExpired'] ? 'bg-red-200' : '', 'border-b-1'])>
                                <td class="px-2 align-top py-2 text-center">
                                    {{ $reference['noKunjungan'] }}
                                </td>
                                <td class="px-2 align-top py-2 text-center">
                                    {{ $reference['tglKunjungan'] }}
                                </td>
                                <td class="px-2 align-top py-2 text-center">
                                    {{ $reference['poliRujukan']['nama'] }}
                                </td>
                                <td class="px-2 align-top py-2">
                                    <div class="line-clamp-2">
                                        {{ $reference['provPerujuk']['kode'] . ' - ' . $reference['provPerujuk']['nama'] }}
                                    </div>
                                </td>
                                <td class="px-2 align-top py-2">
                                    <div class="line-clamp-2">
                                        {{ $reference['diagnosa']['kode'] . ' - ' . $reference['diagnosa']['nama'] }}
                                    </div>
                                </td>
                                <td class="px-2 align-top py-2 text-center">
                                    {{ $reference['expiredAt'] }}
                                </td>
                                <td class="px-2 align-top py-2 text-center">
                                    {{ $reference['isExpired'] ? 'Habis' : 'Aktif' }}
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    @if (!empty($listOfReferences['fktp']))
                        @foreach ($listOfReferences['fktp'] as $reference)
                            <tr @class([$reference['isExpired'] ? 'bg-red-200' : '', 'border-b-1'])>
                                <td class="px-2 align-top py-2 text-center">
                                    {{ $reference['noKunjungan'] }}
                                </td>
                                <td class="px-2 align-top py-2 text-center">
                                    {{ $reference['tglKunjungan'] }}
                                </td>
                                <td class="px-2 align-top py-2 text-center">
                                    {{ $reference['poliRujukan']['nama'] }}
                                </td>
                                <td class="px-2 align-top py-2">
                                    {{ $reference['provPerujuk']['kode'] . ' - ' . $reference['provPerujuk']['nama'] }}
                                </td>
                                <td class="px-2 align-top py-2">
                                    <div class="line-clamp-2">
                                        {{ $reference['diagnosa']['kode'] . ' - ' . $reference['diagnosa']['nama'] }}
                                    </div>
                                </td>
                                <td class="px-2 align-top py-2 text-center">
                                    {{ $reference['expiredAt'] }}
                                </td>
                                <td class="px-2 align-top py-2 text-center">
                                    {{ $reference['isExpired'] ? 'Habis' : 'Aktif' }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        @else
            <div class="text-center">
                Tidak ada rujukan ditemukan
            </div>
        @endif
    </div>
</div>
