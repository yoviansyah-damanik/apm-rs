<?php

namespace App\Helpers;

use App\Models\Register;
use Illuminate\Support\Str;

class MagicHelper
{
    public static function getFullAge($date)
    {
        $interval = date_diff(date_create(), date_create($date));
        return $interval->format("%Y Th, %M Bl, %d Hr");
    }

    public static function getDaysName(int $day): string
    {
        $daysName = '';
        switch ($day) {
            case 0:
                $daysName = 'AKHAD';
                break;
            case 1:
                $daysName = 'SENIN';
                break;
            case 2:
                $daysName = 'SELASA';
                break;
            case 3:
                $daysName = 'RABU';
                break;
            case 4:
                $daysName = 'KAMIS';
                break;
            case 5:
                $daysName = 'JUMAT';
                break;
            case 6:
                $daysName = 'SABTU';
                break;
        }

        return $daysName;
    }

    public static function masking(string|null $text): string
    {
        if (is_string($text)) {
            $textLength = strlen($text);

            $start = $textLength / 4;
            $length = $textLength - ($textLength / 3);

            return Str::mask($text, '*', $start, $length);
        }

        return "NULL";
    }

    public static function format_patient_name(?string $name): ?string
    {
        if (!$name) {
            return $name;
        }

        $name = mb_convert_case(mb_strtolower(trim($name)), MB_CASE_TITLE, "UTF-8");

        // pengecualian gelar
        $exceptions = [
            'Dr' => 'dr',
            'Drg' => 'drg',
            'Hj' => 'Hj',
            'H' => 'H',
        ];

        foreach ($exceptions as $key => $value) {
            $name = preg_replace('/\b' . $key . '\b/u', $value, $name);
        }

        return $name;
    }

    public static function parseEnvArray(mixed $value): array
    {
        if (\is_array($value))
            return $value;
        $decoded = json_decode(str_replace("'", '"', (string) $value), true);
        return \is_array($decoded) ? $decoded : [];
    }
}
