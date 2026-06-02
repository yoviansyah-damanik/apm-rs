<?php

namespace App\Services\BpjsServices;

use App\Models\Patient;
use App\Services\ApiService;
use App\Enums\PurposeOfVisit;
use App\Helpers\SettingHelper;
use App\Services\DecryptionService;
use Illuminate\Support\Facades\Date;

trait VclaimService
{
    /**
     * Ambil data peserta dari Vclaim
     * @return array|array{data: mixed}
     */
    public function getParticipant(): array
    {
        if (!empty($this->participantData))
            return $this->participantData;

        $httpMethod = 'GET';
        $_type = $this->participantNumberType === 'nik' ? 'nik' : 'nokartu';
        $url = $this->baseUrl . "/Peserta/{$_type}/" . $this->participantNumber . '/tglSEP/' . now()->format('Y-m-d');

        try {
            $payload = ApiService::hitApiWithoutParams(
                $url,
                $httpMethod,
                $this->getHeaders()
            );

            // Check if response exists and metadata code is 200
            if (
                !empty($payload['data']['response']) &&
                isset($payload['data']['metaData']['code']) &&
                $payload['data']['metaData']['code'] == 200
            ) {

                $decrypted = DecryptionService::decryptAndDecompress(
                    $this->consId . $this->secretKey . $this->signature['timestamp'],
                    $payload['data']['response']
                );

                $payload = [
                    ...$payload,
                    'data' => json_decode($decrypted['data'], true),
                    'url' => "[$httpMethod] $url"
                ];
            } else {
                // Return error response as-is if code is not 200
                $payload = [
                    'success' => false,
                    'data' => $payload['data'] ?? [
                        'metaData' => [
                            'code' => $payload['data']['metaData']['code'] ?? 500,
                            'message' => $payload['data']['metaData']['message'] ?? 'Failed to get participant data'
                        ]
                    ],
                    'url' => "[$httpMethod] $url"
                ];
            }

            return $payload;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'metaData' => [
                        'code' => 500,
                        'message' => 'Exception: ' . $e->getMessage()
                    ]
                ],
                'url' => "[$httpMethod] $url"
            ];
        }
    }

    /**
     * Ambil data rujukan peserta dari Vclaim
     * @return array|array{data: mixed}
     */
    public function getListOfReferences(
        ?string $noRujukan = null,
    ): array {
        $httpMethod = 'GET';
        $urlFktp = $this->baseUrl . '/Rujukan/List/Peserta/' . $this->participantNumber;
        $urlRs = $this->baseUrl . '/Rujukan/RS/List/Peserta/' . $this->participantNumber;

        $combinedResults = []; // Array untuk menyimpan hasil gabungan dari kedua URL

        try {
            // Panggil API untuk URL FKTP
            $payloadFktp = ApiService::hitApiWithoutParams(
                $urlFktp,
                $httpMethod,
                $this->getHeaders()
            );

            // Proses response dari URL FKTP
            if (
                !empty($payloadFktp['data']['response']) &&
                isset($payloadFktp['data']['metaData']['code']) &&
                $payloadFktp['data']['metaData']['code'] == 200
            ) {
                $decrypted = DecryptionService::decryptAndDecompress(
                    $this->consId . $this->secretKey . $this->signature['timestamp'],
                    $payloadFktp['data']['response']
                );

                $dataFktp = json_decode($decrypted['data'], true);
                $combinedResults['fktp'] = $dataFktp['rujukan'] ?? $dataFktp;
            }

            // Panggil API untuk URL RS
            $payloadRs = ApiService::hitApiWithoutParams(
                $urlRs,
                $httpMethod,
                $this->getHeaders()
            );

            // Proses response dari URL RS
            if (
                !empty($payloadRs['data']['response']) &&
                isset($payloadRs['data']['metaData']['code']) &&
                $payloadRs['data']['metaData']['code'] == 200
            ) {
                $decrypted = DecryptionService::decryptAndDecompress(
                    $this->consId . $this->secretKey . $this->signature['timestamp'],
                    $payloadRs['data']['response']
                );

                $dataRs = json_decode($decrypted['data'], true);
                $combinedResults['rs'] = $dataRs['rujukan'] ?? $dataRs;
            }

            // Filter dan ambil satu nilai jika $noRujukan diisi
            if ($noRujukan) {
                // Gabungkan semua rujukan dari fktp dan rs
                $allReferences = [];

                if (isset($combinedResults['fktp']) && is_array($combinedResults['fktp'])) {
                    $allReferences = array_merge($allReferences, $combinedResults['fktp']);
                }

                if (isset($combinedResults['rs']) && is_array($combinedResults['rs'])) {
                    $allReferences = array_merge($allReferences, $combinedResults['rs']);
                }

                // Cari rujukan berdasarkan noKunjungan
                $filteredReference = collect($allReferences)
                    ->firstWhere('noKunjungan', $noRujukan);

                if ($filteredReference) {
                    // Return hanya 1 data yang ditemukan
                    return [
                        'success' => true,
                        'data' => $filteredReference,
                        'urls' => [
                            'fktp' => "[$httpMethod] $urlFktp",
                            'rs' => "[$httpMethod] $urlRs"
                        ]
                    ];
                } else {
                    // Rujukan tidak ditemukan
                    return [
                        'success' => false,
                        'data' => [
                            'metaData' => [
                                'code' => 404,
                                'message' => "Rujukan dengan nomor {$noRujukan} tidak ditemukan"
                            ]
                        ],
                        'urls' => [
                            'fktp' => "[$httpMethod] $urlFktp",
                            'rs' => "[$httpMethod] $urlRs"
                        ]
                    ];
                }
            }

            // Kembalikan hasil gabungan dari kedua URL
            return [
                'success' => true,
                'data' => $combinedResults,
                'urls' => [
                    'fktp' => "[$httpMethod] $urlFktp",
                    'rs' => "[$httpMethod] $urlRs"
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'metaData' => [
                        'code' => 500,
                        'message' => 'Exception: ' . $e->getMessage()
                    ]
                ],
                'urls' => [
                    'fktp' => "[$httpMethod] $urlFktp",
                    'rs' => "[$httpMethod] $urlRs"
                ]
            ];
        }
    }

    /**
     * Ambil data surat kontrol peserta dari Vclaim
     * @param string|int $serviceType 'all' => Semua Jenis, 1 => Ranap, 2 => Ralan
     * @param string|int $controlType 'all' => Semua Jenis, 1 => SPRI, 2 => Surat Kontrol
     * @param int $totalMonths Jumlah bulan yang diambil (default 3, termasuk bulan ini)
     * @return array|array{data: mixed}
     */
    public function getListOfControlLetters(
        string|int $serviceType = 'all',
        string $controlType = 'all',
        int $totalMonths = 3,
    ): array {
        $httpMethod = 'GET';
        $combinedResults = []; // Array untuk menyimpan hasil gabungan
        $urls = []; // Array untuk menyimpan semua URL yang dipanggil

        try {
            // Loop untuk setiap bulan yang diminta
            for ($i = 0; $i < $totalMonths; $i++) {
                // Hitung bulan dan tahun untuk setiap iterasi
                $date = now()->subMonths($i);
                $month = $date->format('m');
                $year = $date->format('Y');
                $monthName = $date->format('F_Y'); // Nama bulan untuk tracking URL

                // URL untuk bulan saat ini dalam iterasi
                $url = $this->baseUrl . '/RencanaKontrol/ListRencanaKontrol/Bulan/' . $month . '/Tahun/' . $year . '/Nokartu/' . $this->participantNumber . '/filter/2';

                // Simpan URL untuk tracking
                $urls[$monthName] = "[$httpMethod] $url";

                // Panggil API
                $payload = ApiService::hitApiWithoutParams(
                    $url,
                    $httpMethod,
                    $this->getHeaders()
                );

                // Proses response
                if (
                    !empty($payload['data']['response']) &&
                    isset($payload['data']['metaData']['code']) &&
                    $payload['data']['metaData']['code'] == 200
                ) {
                    $decrypted = DecryptionService::decryptAndDecompress(
                        $this->consId . $this->secretKey . $this->signature['timestamp'],
                        $payload['data']['response']
                    );

                    $data = json_decode($decrypted['data'], true);
                    $monthData = $data['list'] ?? $data;

                    // Gabungkan data langsung ke array tanpa key bulan/tahun
                    if (is_array($monthData)) {
                        $combinedResults = array_merge($combinedResults, $monthData);
                    }
                }
            }

            // Filter berdasarkan jnsPelayanan jika type bukan 'all'
            if ($serviceType != 'all') {
                if ($serviceType == 1) {
                    // Filter untuk Rawat Inap
                    $combinedResults = collect($combinedResults)
                        ->filter(fn($item) => isset($item['jnsPelayanan']) && $item['jnsPelayanan'] === 'Rawat Inap')
                        ->values()
                        ->toArray();
                } else if ($serviceType == 2) {
                    // Filter untuk Rawat Jalan
                    $combinedResults = collect($combinedResults)
                        ->filter(fn($item) => isset($item['jnsPelayanan']) && $item['jnsPelayanan'] === 'Rawat Jalan')
                        ->values()
                        ->toArray();
                }
            }

            // Filter berdasarkan jnsKontrol jika type bukan 'all'
            if ($controlType != 'all') {
                if ($controlType == 1) {
                    // Filter untuk Rawat Inap
                    $combinedResults = collect($combinedResults)
                        ->filter(fn($item) => isset($item['jnsKontrol']) && $item['jnsKontrol'] === '1')
                        ->values()
                        ->toArray();
                } else if ($controlType == 2) {
                    // Filter untuk Rawat Jalan
                    $combinedResults = collect($combinedResults)
                        ->filter(fn($item) => isset($item['jnsKontrol']) && $item['jnsKontrol'] === '2')
                        ->values()
                        ->toArray();
                }
            }

            // Transform data ke format yang dibutuhkan blade
            // $transformedResults = collect($combinedResults)->map(function ($item) {
            //     $tglRencana = \Carbon\Carbon::parse($item['tglRencanaKontrol']);
            //     $today = \Carbon\Carbon::today();

            //     // Check apakah sudah menerbitkan SEP
            //     $hasSep = isset($item['terbitSEP']) && $item['terbitSEP'] === 'Sudah';

            //     // Check apakah tanggal rencana kontrol belum tiba (H-3)
            //     $isNotYetTime = $tglRencana->diffInDays($today, false) < -3;

            //     // Generate message untuk tanggal yang belum tiba
            //     $dateNotArrivedMessage = null;
            //     if ($isNotYetTime) {
            //         $daysUntil = abs($tglRencana->diffInDays($today));
            //         $dateNotArrivedMessage = "Tanggal kontrol masih {$daysUntil} hari lagi (dapat digunakan H-3)";
            //     }

            //     return [
            //         'no_surat' => $item['noSuratKontrol'] ?? null,
            //         'jns_pelayanan' => $item['jnsPelayanan'] ?? null,
            //         'jns_kontrol' => $item['jnsKontrol'] ?? null,
            //         'nama_jns_kontrol' => $item['namaJnsKontrol'] ?? null,
            //         'tgl_rencana' => $item['tglRencanaKontrol'] ?? null,
            //         'tgl_terbit' => $item['tglTerbitKontrol'] ?? null,
            //         'no_sep_asal' => $item['noSepAsalKontrol'] ?? null,
            //         'no_rujukan' => $item['noSepAsalKontrol'] ?? null, // Untuk compatibility
            //         'kd_poli_asal' => $item['poliAsal'] ?? null,
            //         'nm_poli_asal' => $item['namaPoliAsal'] ?? null,
            //         'kd_poli_bpjs' => $item['poliTujuan'] ?? null,
            //         'nm_poli_bpjs' => $item['namaPoliTujuan'] ?? null,
            //         'tgl_sep' => $item['tglSEP'] ?? null,
            //         'kd_dokter_bpjs' => $item['kodeDokter'] ?? null,
            //         'nm_dokter_bpjs' => $item['namaDokter'] ?? null,
            //         'no_kartu' => $item['noKartu'] ?? null,
            //         'nama_pasien' => $item['nama'] ?? null,
            //         'terbit_sep' => $item['terbitSEP'] ?? null,
            //         'hasSep' => $hasSep,
            //         'isNotYetTime' => $isNotYetTime,
            //         'dateNotArrivedMessage' => $dateNotArrivedMessage,
            //     ];
            // })->toArray();

            // Kembalikan hasil gabungan dari semua bulan
            return [
                'success' => true,
                'data' => $combinedResults,
                'urls' => $urls
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'metaData' => [
                        'code' => 500,
                        'message' => 'Exception: ' . $e->getMessage()
                    ]
                ],
                'urls' => $urls
            ];
        }
    }

    /**
     * Ambil rujukan
     * @param ?string $polyclinic    Kode Poli BPJS
     * @return array|array{data: mixed}
     */
    public function getReferal(
        ?string $polyclinic = null,
    ) {
        $httpMethod = 'GET';
        $url = $this->baseUrl . '/Rujukan/List/Peserta/' . $this->participantNumber;

        try {
            $payload = ApiService::hitApiWithoutParams(
                $url,
                $httpMethod,
                $this->getHeaders()
            );

            // Check if response exists and metadata code is 200
            if (
                !empty($payload['data']['response']) &&
                isset($payload['data']['metaData']['code']) &&
                $payload['data']['metaData']['code'] == 200
            ) {

                $decrypted = DecryptionService::decryptAndDecompress(
                    $this->consId . $this->secretKey . $this->signature['timestamp'],
                    $payload['data']['response']
                );

                $payload = [
                    ...$payload,
                    'data' => [
                        'metaData' => $payload['data']['metaData'],
                        'response' => json_decode($decrypted['data'], true)
                    ]
                ];

                if ($polyclinic && isset($payload['data']['response']['rujukan'])) {
                    $payload = [
                        ...$payload,
                        'data' => [
                            'metaData' => $payload['data']['metaData'],
                            'response' => collect($payload['data']['response']['rujukan'])
                                ->where('poliRujukan.kode', $polyclinic)
                                ->first()
                        ],
                        'url' => "[$httpMethod] $url"
                    ];
                } else {
                    $payload = [
                        ...$payload,
                        'url' => "[$httpMethod] $url"
                    ];
                }
            } else {
                // Return error response
                $payload = [
                    'success' => false,
                    'data' => $payload['data'] ?? [
                        'metaData' => [
                            'code' => $payload['data']['metaData']['code'] ?? 500,
                            'message' => $payload['data']['metaData']['message'] ?? 'Failed to get referal'
                        ]
                    ],
                    'url' => "[$httpMethod] $url"
                ];
            }

            return $payload;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'metaData' => [
                        'code' => 500,
                        'message' => 'Exception: ' . $e->getMessage()
                    ]
                ],
                'url' => "[$httpMethod] $url"
            ];
        }
    }

    public function getHistoryMonitoring(
        ?int $serviceType = null,
        ?string $polyclinic = null,
        bool $pickOne = false
    ) {
        $httpMethod = 'GET';
        $startDate = now()->subDays(90)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');
        $url = $this->baseUrl . '/monitoring/HistoriPelayanan/NoKartu/' . $this->participantNumber . '/tglMulai/' . $startDate . '/tglAkhir/' . $endDate;

        try {
            $payload = ApiService::hitApiWithoutParams(
                $url,
                $httpMethod,
                $this->getHeaders(
                    contentType: 'application/json'
                )
            );

            // Check if response exists and metadata code is 200
            if (
                !empty($payload['data']['response']) &&
                isset($payload['data']['metaData']['code']) &&
                $payload['data']['metaData']['code'] == 200
            ) {

                $decrypted = DecryptionService::decryptAndDecompress(
                    $this->consId . $this->secretKey . $this->signature['timestamp'],
                    $payload['data']['response']
                );

                $responseData = json_decode($decrypted['data'], true);

                if (isset($responseData['histori'])) {
                    $data = collect($responseData['histori'])
                        ->sortByDesc(fn($item) => \Carbon\Carbon::parse($item['tglPlgSep']));

                    if (in_array($serviceType, [1, 2])) {
                        $data = $data->where('jnsPelayanan', $serviceType);

                        if ($serviceType === 1)
                            $data = $data->where('tglPlgSep', '<>', null);
                    }

                    if ($polyclinic)
                        $data = $data->where('poli', $polyclinic);

                    if ($pickOne)
                        $data = $data->first();

                    $payload = [
                        ...$payload,
                        'data' => [
                            'metaData' => $payload['data']['metaData'],
                            'response' => $data
                        ],
                        'url' => "[$httpMethod] $url"
                    ];
                } else {
                    $payload = [
                        ...$payload,
                        'url' => "[$httpMethod] $url"
                    ];
                }
            } else {
                // Return error response
                $payload = [
                    'success' => false,
                    'data' => $payload['data'] ?? [
                        'metaData' => [
                            'code' => $payload['data']['metaData']['code'] ?? 500,
                            'message' => $payload['data']['metaData']['message'] ?? 'Failed to get history monitoring'
                        ]
                    ],
                    'url' => "[$httpMethod] $url"
                ];
            }

            return $payload;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'metaData' => [
                        'code' => 500,
                        'message' => 'Exception: ' . $e->getMessage()
                    ]
                ],
                'url' => "[$httpMethod] $url"
            ];
        }
    }

    /**
     * Ambil surat kontrol
     * @param     string          $refDate        Tanggal Rujukan (Format: Y-m-d)
     * @param     ?string         $polyclinic     Filter kode poli
     * @param     ?int            $serviceType    1. Ranap | 2. Ralan
     * @param     bool            $pickOne        Ambil hanya 1 data terakhir
     * @return array|array{data: mixed}
     */
    public function getControlNumber(
        string $refDate,
        ?string $polyclinic = null,
        ?int $controlType = null,
        PurposeOfVisit $purposeOfVisit,
        bool $pickOne = false
    ) {
        $httpMethod = 'GET';
        $startDate = now()->subDays(29)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');
        $url = $this->baseUrl . '/RencanaKontrol/ListRencanaKontrol/tglAwal/' . $startDate . '/tglAkhir/' . $endDate . '/filter/2';

        try {
            $payload = ApiService::hitApiWithoutParams(
                $url,
                $httpMethod,
                $this->getHeaders()
            );

            // Check if response exists and metadata code is 200
            if (
                !empty($payload['data']['response']) &&
                isset($payload['data']['metaData']['code']) &&
                $payload['data']['metaData']['code'] == 200
            ) {

                $decrypted = DecryptionService::decryptAndDecompress(
                    $this->consId . $this->secretKey . $this->signature['timestamp'],
                    $payload['data']['response']
                );

                $responseData = json_decode($decrypted['data'], true);
                if (isset($responseData['list'])) {
                    $data = collect($responseData['list'])
                        ->where('terbitSEP', 'Belum')
                        ->where('noKartu', $this->participantNumber)
                        ->where('namaJnsKontrol', 'Surat Kontrol')
                        ->where('jnsKontrol', "{$controlType}")
                        ->whereBetween('tglRencanaKontrol', [$refDate, \Carbon\Carbon::parse($refDate)->addDays(90)->format('Y-m-d')])
                        ->sortByDesc(fn($item) => \Carbon\Carbon::parse($item['tglRencanaKontrol']));

                    if ($polyclinic)
                        $data = $data->where('poliTujuan', $polyclinic);

                    if ($purposeOfVisit->value == PurposeOfVisit::KontrolPostRanap->value)
                        $data = $data->where('jnsPelayanan', 'Rawat Inap');
                    else
                        $data = $data->where('jnsPelayanan', 'Rawat Jalan');

                    if ($pickOne)
                        $data = $data->first();

                    $payload = [
                        ...$payload,
                        'data' => $data,
                        'url' => "[$httpMethod] $url"
                    ];
                } else {
                    $payload = [
                        ...$payload,
                        'url' => "[$httpMethod] $url"
                    ];
                }
            } else {
                // Return error response
                $payload = [
                    'success' => false,
                    'data' => $payload['data'] ?? [
                        'metaData' => [
                            'code' => $payload['data']['metaData']['code'] ?? 500,
                            'message' => $payload['data']['metaData']['message'] ?? 'Failed to get control number'
                        ]
                    ],
                    'url' => "[$httpMethod] $url"
                ];
            }

            return $payload;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'metaData' => [
                        'code' => 500,
                        'message' => 'Exception: ' . $e->getMessage()
                    ]
                ],
                'url' => "[$httpMethod] $url"
            ];
        }
    }

    public function findControlNumber(
        string $controlNumber
    ) {
        $httpMethod = 'GET';
        $url = $this->baseUrl . '/RencanaKontrol/noSuratKontrol/' . $controlNumber;

        try {
            $payload = ApiService::hitApiWithoutParams(
                $url,
                $httpMethod,
                $this->getHeaders()
            );

            // Check if response exists and metadata code is 200
            if (
                !empty($payload['data']['response']) &&
                isset($payload['data']['metaData']['code']) &&
                $payload['data']['metaData']['code'] == 200
            ) {
                $decrypted = DecryptionService::decryptAndDecompress(
                    $this->consId . $this->secretKey . $this->signature['timestamp'],
                    $payload['data']['response']
                );

                $data = json_decode($decrypted['data'], true);

                $payload = [
                    'success' => true,
                    'data' => [
                        'metaData' => $payload['data']['metaData'],
                        'response' => $data
                    ],
                    'url' => "[$httpMethod] $url"
                ];
            } else {
                // Return error response
                $payload = [
                    'success' => false,
                    'data' => $payload['data'] ?? [
                        'metaData' => [
                            'code' => $payload['data']['metaData']['code'] ?? 500,
                            'message' => $payload['data']['metaData']['message'] ?? 'Failed to get control number'
                        ]
                    ],
                    'url' => "[$httpMethod] $url"
                ];
            }

            return $payload;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'metaData' => [
                        'code' => 500,
                        'message' => 'Exception: ' . $e->getMessage()
                    ]
                ],
                'url' => "[$httpMethod] $url"
            ];
        }
    }
    public function updateControlNumber(
        string $controlNumber,
        string $sepNumber,
        string $doctor,
        string $polyclinic,
        string $refDate,
    ) {
        $httpMethod = 'PUT';
        $url = $this->baseUrl . '/RencanaKontrol/Update';

        try {
            $data = [
                "request" => [
                    'noSuratKontrol' => $controlNumber,
                    'noSEP' => $sepNumber,
                    'kodeDokter' => $doctor,
                    'poliKontrol' => $polyclinic,
                    'tglRencanaKontrol' => $refDate,
                    'user' => 'Service APM'
                ]
            ];

            $payload = ApiService::hitApiWithParams(
                $url,
                $data,
                $httpMethod,
                $this->getHeaders()
            );

            if (
                !empty($payload['data']['response']) &&
                isset($payload['data']['metaData']['code']) &&
                $payload['data']['metaData']['code'] == 200
            ) {
                $payload = [
                    'success' => true,
                    'data' => [
                        'metaData' => $payload['data']['metaData'] ?? [
                            'code' => $payload['data']['metaData']['code'] ?? 500,
                            'message' => $payload['data']['metaData']['message'] ?? 'Failed to insert SEP'
                        ],
                        'response' => $payload['data']['response']
                    ],
                    'url' => "[$httpMethod] $url"
                ];
            } else {
                // Return error response
                $payload = [
                    'success' => false,
                    'data' => [
                        'metaData' => $payload['data']['metaData'] ?? [
                            'code' => $payload['data']['metaData']['code'] ?? 500,
                            'message' => $payload['data']['metaData']['message'] ?? 'Failed to insert SEP'
                        ],
                        'requestData' => $data
                    ],
                    'url' => "[$httpMethod] $url"
                ];
            }
            return $payload;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'metaData' => [
                        'code' => 500,
                        'message' => 'Exception: ' . $e->getMessage()
                    ],
                    'requestData' => $data ?? null
                ],
                'url' => "[$httpMethod] $url"
            ];
        }
    }

    public function checkControlNumber(
        string $controlNumber,
    ) {
        $httpMethod = 'GET';
        $url = $this->baseUrl . '/RencanaKontrol/noSuratKontrol/' . $controlNumber;

        try {
            $payload = ApiService::hitApiWithoutParams(
                $url,
                $httpMethod,
                $this->getHeaders()
            );

            // Check if response exists and metadata code is 200
            if (
                !empty($payload['data']['response']) &&
                isset($payload['data']['metaData']['code']) &&
                $payload['data']['metaData']['code'] == 200
            ) {

                $decrypted = DecryptionService::decryptAndDecompress(
                    $this->consId . $this->secretKey . $this->signature['timestamp'],
                    $payload['data']['response']
                );

                $payload = [
                    ...$payload,
                    'data' => [
                        'metaData' => $payload['data']['metaData'],
                        'response' => json_decode($decrypted['data'], true)
                    ],
                    'url' => "[$httpMethod] $url"
                ];
            } else {
                // Return error response
                $payload = [
                    'success' => false,
                    'data' => $payload['data'] ?? [
                        'metaData' => [
                            'code' => $payload['data']['metaData']['code'] ?? 500,
                            'message' => $payload['data']['metaData']['message'] ?? 'Failed to check control number'
                        ]
                    ],
                    'url' => "[$httpMethod] $url"
                ];
            }

            return $payload;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'metaData' => [
                        'code' => 500,
                        'message' => 'Exception: ' . $e->getMessage()
                    ]
                ],
                'url' => "[$httpMethod] $url"
            ];
        }
    }

    /**
     * Cek status biometrik
     * @param \Carbon\Carbon|Date|null $date
     * @return array|array{data: mixed}
     */
    public function getBiometricStatus(\Carbon\Carbon|Date|null $date = null): array
    {
        $date = $date ? $date->format('Y-m-d') : now()->format('Y-m-d');
        $httpMethod = 'GET';

        try {
            // LANGKAH 1: Cek Antrol apakah pasien perlu fingerprint
            // Jika daftarfp == false, pasien tidak perlu fingerprint
            // Jika daftarfp == true, lanjut ke Vclaim untuk cek status biometric

            $this->setService('antrol');
            $antrolUrl = $this->baseUrl . '/ref/pasien/fp/identitas/noka/noidentitas/' . $this->participantNumber;
            $antrolPayload = ApiService::hitApiWithoutParams(
                $antrolUrl,
                $httpMethod,
                $this->getHeaders()
            );

            // Cek response Antrol
            if (
                !isset($antrolPayload['data']) ||
                $antrolPayload['data']['metadata']['code'] != 1
            ) {
                // Error dari Antrol
                return [
                    'success' => false,
                    'data' => [
                        'kode' => $antrolPayload['data']['metadata']['code'] ?? 0,
                        'message' => $antrolPayload['data']['metadata']['message'] ?? 'Gagal mengecek status fingerprint di Antrol',
                        'payload' => $antrolPayload
                    ],
                    'url' => "[$httpMethod] $antrolUrl"
                ];
            }

            // Decrypt response Antrol
            $antrolDecrypted = DecryptionService::decryptAndDecompress(
                $this->consId . $this->secretKey . $this->signature['timestamp'],
                $antrolPayload['data']['response']
            );
            $antrolData = json_decode($antrolDecrypted['data'], true);

            // Cek apakah pasien perlu fingerprint
            $daftarFp = $antrolData['daftarfp'] ?? false;

            if (!$daftarFp) {
                // Pasien tidak perlu melakukan fingerprint
                return [
                    'success' => true,
                    'data' => [
                        'kode' => 1,
                        'biometric_required' => false,
                        'status' => 'Pasien tidak perlu melakukan fingerprint'
                    ],
                    'url' => "[$httpMethod] $antrolUrl"
                ];
            }

            // LANGKAH 2: Pasien perlu fingerprint, cek status di Vclaim
            $this->setService('vclaim');
            $vclaimUrl = $this->baseUrl . '/SEP/FingerPrint/Peserta/' . $this->participantNumber . '/TglPelayanan/' . $date;
            $vclaimPayload = ApiService::hitApiWithoutParams(
                $vclaimUrl,
                $httpMethod,
                $this->getHeaders()
            );

            // Cek response Vclaim dan kode metadata adalah 200
            if (
                !empty($vclaimPayload['data']['response']) &&
                isset($vclaimPayload['data']['metaData']['code']) &&
                $vclaimPayload['data']['metaData']['code'] == 200
            ) {
                $decrypted = DecryptionService::decryptAndDecompress(
                    $this->consId . $this->secretKey . $this->signature['timestamp'],
                    $vclaimPayload['data']['response']
                );

                // return [
                //     'success' => true,
                //     'data' => [
                //         'biometric_required' => false,
                //         'status' => 'Pasien tidak perlu melakukan fingerprint'
                //     ]
                // ];
                $data = json_decode($decrypted['data'], true);

                $payload = [
                    'data' => [
                        'biometric_required' => true,
                        'kode' => $data['kode'],
                        'status' => $data['kode'] == 0 ? 'Pasien belum melakukan fingerprint' : 'Pasien sudah melakukan fingerprint'
                    ],
                    'url' => "[$httpMethod] $vclaimUrl"
                ];
            } else {
                // Return error response dari Vclaim
                $payload = [
                    'success' => false,
                    'data' => $vclaimPayload['data'] ?? [
                        'kode' => $vclaimPayload['data']['metaData']['code'] ?? 0,
                        'message' => $vclaimPayload['data']['metaData']['message'] ?? 'Gagal mengecek status biometric di Vclaim',
                        'tes' => 'tes'
                    ],
                    'url' => "[$httpMethod] $vclaimUrl"
                ];
            }

            return $payload;
        } catch (\Exception $e) {
            $errorUrl = $vclaimUrl ?? $antrolUrl ?? null;
            return [
                'success' => false,
                'data' => [
                    'code' => 500,
                    'message' => 'Exception: ' . $e->getMessage()
                ],
                'url' => $errorUrl ? "[$httpMethod] $errorUrl" : null
            ];
        }
    }

    /* "tujuanKunj":        {"0": Normal,
     *                       "1": Prosedur,
     *                       "2": Konsul Dokter},
     * "flagProcedure":      {"0": Prosedur Tidak Berkelanjutan,
     *                       "1": Prosedur dan Terapi Berkelanjutan} ==> diisi "" jika tujuanKunj = "0",
     * "kdPenunjang":        {"1": Radioterapi,
     *                       "2": Kemoterapi,
     *                       "3": Rehabilitasi Medik,
     *                       "4": Rehabilitasi Psikososial,
     *                       "5": Transfusi Darah,
     *                       "6": Pelayanan Gigi,
     *                       "7": Laboratorium,
     *                       "8": USG,
     *                       "9": Farmasi,
     *                       "10": Lain-Lain,
     *                       "11": MRI,
     *                       "12": HEMODIALISA} ==> diisi "" jika tujuanKunj = "0",
     * "assesmentPel":       {"1": Poli spesialis tidak tersedia pada hari sebelumnya,
     *                       "2": Jam Poli telah berakhir pada hari sebelumnya,
     *                       "3": Dokter Spesialis yang dimaksud tidak praktek pada hari sebelumnya,
     *                       "4": Atas Instruksi RS} ==> diisi jika tujuanKunj = "2" atau "0" (politujuan beda dengan poli rujukan dan hari beda),
     *                       "5": Tujuan Kontrol,
     */

    public function insertSEP(
        array|Patient $patient,
        array $participantData,
        array $doctor,
        array $polyclinic,
        array $referralData,
        string|PurposeOfVisit $purposeOfVisit,
    ) {
        $httpMethod = 'POST';
        $url = $this->baseUrl . '/SEP/2.0/insert';

        try {
            $requestData = [
                "request" => [
                    "t_sep" => [
                        "noKartu" => $participantData['peserta']['noKartu'],
                        "tglSep" => now()->format('Y-m-d'),
                        "ppkPelayanan" => SettingHelper::get('hospitalBpjsCode'),
                        "jnsPelayanan" => "{$referralData['serviceType']}",
                        "klsRawat" => [
                            "klsRawatHak" => "{$referralData['classRights']}",
                            "klsRawatNaik" => "",
                            "pembiayaan" => "",
                            "penanggungJawab" => ""
                        ],
                        "noMR" => $patient['no_rkm_medis'],
                        "rujukan" => [
                            "asalRujukan" => $referralData['refOriginVisit'],
                            "tglRujukan" => $referralData['refDate'],
                            "noRujukan" => $referralData['refNumber'],
                            "ppkRujukan" => $referralData['refOriginId']
                        ],
                        "catatan" => $purposeOfVisit->value,
                        "diagAwal" => $referralData['diagnoseId'],
                        "poli" => [
                            "tujuan" => $polyclinic['polyclinicId'],
                            "eksekutif" => $referralData['executive'] ? '1' : '0'
                        ],
                        "cob" => [
                            "cob" => $referralData['cob'] ? '1' : '0'
                        ],
                        "katarak" => [
                            "katarak" => $referralData['cataract'] ? '1' : '0'
                        ],
                        "jaminan" => [
                            "lakaLantas" => "0",
                            "noLP" => "",
                            "penjamin" => [
                                "tglKejadian" => "",
                                "keterangan" => "",
                                "suplesi" => [
                                    "suplesi" => "0",
                                    "noSepSuplesi" => "",
                                    "lokasiLaka" => [
                                        "kdPropinsi" => "",
                                        "kdKabupaten" => "",
                                        "kdKecamatan" => ""
                                    ]
                                ]
                            ]
                        ],
                        "tujuanKunj" => $purposeOfVisit->name == PurposeOfVisit::Kontrol->name ? "2" : "0",
                        "flagProcedure" => $referralData['procedureFlag'],
                        "kdPenunjang" => $referralData['support'],
                        "assesmentPel" => $referralData['serviceAssessment'],
                        "skdp" => [
                            "noSurat" => in_array($purposeOfVisit->name, [PurposeOfVisit::Kontrol->name, PurposeOfVisit::KontrolPostRanap->name]) ? $referralData['controlNumber'] : "",
                            "kodeDPJP" => $doctor['doctorId']
                        ],
                        "dpjpLayan" => $doctor['doctorId'],
                        "noTelp" => (string) $patient['no_tlp'],
                        "user" => "APM"
                    ]
                ]
            ];

            $payload = ApiService::hitApiWithParams(
                $url,
                $requestData,
                $httpMethod,
                $this->getHeaders()
            );

            // Check if response exists and metadata code is 200
            if (
                !empty($payload['data']['response']) &&
                isset($payload['data']['metaData']['code']) &&
                $payload['data']['metaData']['code'] == 200
            ) {
                $decrypted = DecryptionService::decryptAndDecompress(
                    $this->consId . $this->secretKey . $this->signature['timestamp'],
                    $payload['data']['response']
                );

                $responseData = json_decode($decrypted['data'], true);

                if (isset($responseData['sep'])) {
                    $data = collect($responseData['sep']);

                    $payload = [
                        ...$payload,
                        'data' => [
                            'metaData' => $payload['data']['metaData'],
                            'response' => $data,
                            'requestData' => $requestData
                        ],
                        'url' => "[$httpMethod] $url"
                    ];
                }
            } else {
                // Return error response
                $payload = [
                    'success' => false,
                    'data' => [
                        'metaData' => $payload['data']['metaData'] ?? [
                            'code' => $payload['data']['metaData']['code'] ?? 500,
                            'message' => $payload['data']['metaData']['message'] ?? 'Failed to insert SEP'
                        ],
                        'requestData' => $requestData
                    ],
                    'url' => "[$httpMethod] $url"
                ];
            }

            return $payload;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'metaData' => [
                        'code' => 500,
                        'message' => 'Exception: ' . $e->getMessage()
                    ],
                    'requestData' => $requestData ?? null
                ],
                'url' => "[$httpMethod] $url"
            ];
        }
    }

    /**
     * Ambil SEP terakhir peserta berdasarkan nomor rujukan
     */
    public function getLastSEPByRujukan(string $noRujukan): array
    {
        $httpMethod = 'GET';
        $url = $this->baseUrl . '/Rujukan/lastsep/norujukan/' . $noRujukan;

        try {
            $payload = ApiService::hitApiWithoutParams($url, $httpMethod, $this->getHeaders());

            if (
                !empty($payload['data']['response']) &&
                isset($payload['data']['metaData']['code']) &&
                $payload['data']['metaData']['code'] == 200
            ) {
                $decrypted = DecryptionService::decryptAndDecompress(
                    $this->consId . $this->secretKey . $this->signature['timestamp'],
                    $payload['data']['response']
                );

                $payload = [
                    ...$payload,
                    'data' => json_decode($decrypted['data'], true),
                    'url' => "[$httpMethod] $url"
                ];
            } else {
                $payload = [
                    'success' => false,
                    'data' => $payload['data'] ?? [
                        'metaData' => [
                            'code' => $payload['data']['metaData']['code'] ?? 500,
                            'message' => $payload['data']['metaData']['message'] ?? 'Failed to get last SEP'
                        ]
                    ],
                    'url' => "[$httpMethod] $url"
                ];
            }

            return $payload;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'metaData' => [
                        'code' => 500,
                        'message' => 'Exception: ' . $e->getMessage()
                    ]
                ],
                'url' => "[$httpMethod] $url"
            ];
        }
    }

    /**
     * Cari referensi faskes (FKTP atau RS) berdasarkan kata kunci
     * @param string $keyword   Nama atau kode faskes
     * @param int    $jenis     1 = FKTP, 2 = RS
     */
    public function getReferensiFaskes(string $keyword, int $jenis): array
    {
        $httpMethod = 'GET';
        $url = $this->baseUrl . "/referensi/faskes/{$keyword}/{$jenis}";

        try {
            $payload = ApiService::hitApiWithoutParams($url, $httpMethod, $this->getHeaders());

            if (
                !empty($payload['data']['response']) &&
                isset($payload['data']['metaData']['code']) &&
                $payload['data']['metaData']['code'] == 200
            ) {
                $decrypted = DecryptionService::decryptAndDecompress(
                    $this->consId . $this->secretKey . $this->signature['timestamp'],
                    $payload['data']['response']
                );

                $data = json_decode($decrypted['data'], true);

                return [
                    'success' => true,
                    'data' => $data['faskes'] ?? [],
                ];
            }

            return ['success' => false, 'data' => []];
        } catch (\Exception $e) {
            return ['success' => false, 'data' => []];
        }
    }
}

