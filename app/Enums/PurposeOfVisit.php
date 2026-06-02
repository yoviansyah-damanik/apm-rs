<?php

namespace App\Enums;

enum PurposeOfVisit: string
{
    case RujukPertama = 'Rujukan Pertama';
    case Kontrol = 'Kontrol';
    case KontrolPostRanap = 'Kontrol (Post Ranap)';
    case RujukInternal = 'Rujukan Internal';
    case KonsulInternal = 'Konsul Internal';

    /** Group 1: RujukPertama | Group 2: Kontrol | Group 3: Internal */
    public function group(): string
    {
        return match ($this) {
            self::RujukPertama                    => 'RujukPertama',
            self::Kontrol, self::KontrolPostRanap => 'Kontrol',
            self::RujukInternal, self::KonsulInternal => 'Internal',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::RujukPertama => 'home',
            self::Kontrol => 'arrow-path',
            self::KontrolPostRanap => 'home',
            self::RujukInternal => 'arrows-right-left',
            self::KonsulInternal => 'chat-bubble-left-right',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::RujukPertama => 'Kunjungan pertama dari rujukan fasilitas kesehatan tingkat pertama',
            self::Kontrol => 'Kunjungan kontrol/tindak lanjut dari poliklinik yang sama',
            self::KontrolPostRanap => 'Kunjungan kontrol setelah menjalani rawat inap',
            self::RujukInternal => 'Dirujuk ke poliklinik lain dalam rumah sakit yang sama',
            self::KonsulInternal => 'Konsultasi antar dokter spesialis dalam rumah sakit',
        };
    }
}
