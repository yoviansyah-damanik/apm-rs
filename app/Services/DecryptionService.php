<?php

namespace App\Services;

use Exception;

class DecryptionService
{
    /**
     * Dekripsi string menggunakan AES-256-CBC
     *
     * @param string $key - Kunci dekripsi
     * @param string $encryptedString - String yang sudah dienkripsi (base64)
     * @return string|false - String hasil dekripsi atau false jika gagal
     */
    public static function stringDecrypt(string $key, string $encryptedString)
    {
        try {
            $encrypt_method = 'AES-256-CBC';

            // Validasi input
            if (empty($key) || empty($encryptedString)) {
                throw new Exception('Key dan encrypted string tidak boleh kosong');
            }

            // Hash key menggunakan SHA256 dan convert ke binary
            $key_hash = hex2bin(hash('sha256', $key));

            // IV - AES-256-CBC membutuhkan 16 bytes
            $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);

            // Dekripsi
            $output = openssl_decrypt(
                base64_decode($encryptedString),
                $encrypt_method,
                $key_hash,
                OPENSSL_RAW_DATA,
                $iv
            );

            return $output;
        } catch (Exception $e) {
            // Log error jika diperlukan
            \Log::error('Decryption error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Dekripsi string dengan validasi yang lebih ketat
     *
     * @param string $key
     * @param string $encryptedString
     * @return array - Array dengan success status dan data
     */
    public static function secureStringDecrypt(string $key, string $encryptedString): array
    {
        try {
            $encrypt_method = 'AES-256-CBC';

            // Validasi input
            if (empty($key)) {
                throw new Exception('Key dekripsi tidak boleh kosong');
            }

            if (empty($encryptedString)) {
                throw new Exception('String yang akan didekripsi tidak boleh kosong');
            }

            // Validasi apakah string valid base64
            if (!base64_decode($encryptedString, true)) {
                throw new Exception('String bukan format base64 yang valid');
            }

            // Hash key
            $key_hash = hex2bin(hash('sha256', $key));
            if (!$key_hash) {
                throw new Exception('Gagal memproses key');
            }

            // Generate IV
            $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);

            // Dekripsi
            $decrypted = openssl_decrypt(
                base64_decode($encryptedString),
                $encrypt_method,
                $key_hash,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($decrypted === false) {
                throw new Exception('Gagal melakukan dekripsi. Key atau data mungkin salah.');
            }

            return [
                'success' => true,
                'data' => $decrypted,
                'error' => null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Fungsi untuk decompress LZ String
     * Membutuhkan library: composer require nullpunkt/lz-string-php
     *
     * @param string $compressedString
     * @return string|false
     */
    public static function decompress(string $compressedString)
    {
        try {
            // Pastikan library LZ String sudah terinstall
            if (!class_exists('\LZCompressor\LZString')) {
                throw new Exception('Library LZ String tidak ditemukan. Install dengan: composer require nullpunkt/lz-string-php');
            }

            if (empty($compressedString)) {
                throw new Exception('String yang akan di-decompress tidak boleh kosong');
            }

            $decompressed = \LZCompressor\LZString::decompressFromEncodedURIComponent($compressedString);

            return $decompressed;
        } catch (Exception $e) {
            \Log::error('Decompression error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Decompress dengan validasi yang lebih ketat
     *
     * @param string $compressedString
     * @return array
     */
    public static function secureDecompress(string $compressedString): array
    {
        try {
            if (!class_exists('\LZCompressor\LZString')) {
                throw new Exception('Library LZ String tidak ditemukan. Install dengan: composer require nullpunkt/lz-string-php');
            }

            if (empty($compressedString)) {
                throw new Exception('String yang akan di-decompress tidak boleh kosong');
            }

            $decompressed = \LZCompressor\LZString::decompressFromEncodedURIComponent($compressedString);

            if ($decompressed === false || $decompressed === null) {
                throw new Exception('Gagal melakukan decompression. Data mungkin corrupt atau format salah.');
            }

            return [
                'success' => true,
                'data' => $decompressed,
                'error' => null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Kombinasi dekripsi dan decompress
     *
     * @param string $key
     * @param string $encryptedCompressedString
     * @return array
     */
    public static function decryptAndDecompress(string $key, string $encryptedCompressedString): array
    {
        try {
            // Step 1: Dekripsi
            $decryptResult = self::secureStringDecrypt($key, $encryptedCompressedString);

            if (!$decryptResult['success']) {
                return $decryptResult;
            }

            // Step 2: Decompress
            $decompressResult = self::secureDecompress($decryptResult['data']);

            if (!$decompressResult['success']) {
                return $decompressResult;
            }

            return [
                'success' => true,
                'data' => $decompressResult['data'],
                'error' => null,
                'steps' => ['decrypted', 'decompressed']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'error' => $e->getMessage(),
                'steps' => []
            ];
        }
    }

    /**
     * Utility function untuk enkripsi (untuk testing)
     *
     * @param string $key
     * @param string $plainText
     * @return string
     */
    public static function stringEncrypt(string $key, string $plainText): string
    {
        $encrypt_method = 'AES-256-CBC';
        $key_hash = hex2bin(hash('sha256', $key));
        $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);

        $encrypted = openssl_encrypt($plainText, $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

        return base64_encode($encrypted);
    }

    /**
     * Utility function untuk compress (untuk testing)
     *
     * @param string $plainText
     * @return string
     */
    public static function compress(string $plainText): string
    {
        if (!class_exists('\LZCompressor\LZString')) {
            throw new Exception('Library LZ String tidak ditemukan');
        }

        return \LZCompressor\LZString::compressToEncodedURIComponent($plainText);
    }
}
