<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\ConnectionException;
use Exception;
use App\Exceptions\BpjsTimeoutException;

class ApiService
{
    /**
     * Hit API tanpa parameter
     *
     * @param string $url
     * @param string $method (GET, POST, PUT, DELETE)
     * @param array $headers
     * @param int $timeout
     * @return array
     */
    public static function hitApiWithoutParams(
        string $url,
        string $method = 'GET',
        array $headers = [],
        int $timeout = 30
    ): array {
        try {
            $httpClient = Http::timeout($timeout);

            // Set headers jika ada
            if (!empty($headers)) {
                $httpClient = $httpClient->withHeaders($headers);
            }

            // Pilih method HTTP
            $response = match (strtoupper($method)) {
                'GET' => $httpClient->get($url),
                'POST' => $httpClient->post($url),
                'PUT' => $httpClient->put($url),
                'DELETE' => $httpClient->delete($url),
                default => $httpClient->get($url)
            };

            return [
                'url' => $url,
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'data' => $response->json(),
                'headers' => $response->headers(),
                'error' => null
            ];
        } catch (ConnectionException $e) {
            // Detect timeout error
            $errorMessage = $e->getMessage();
            if (
                stripos($errorMessage, 'timeout') !== false ||
                stripos($errorMessage, 'timed out') !== false ||
                stripos($errorMessage, 'Maximum execution time') !== false
            ) {
                throw new BpjsTimeoutException();
            }

            return [
                'url' => $url,
                'success' => false,
                'status_code' => 0,
                'data' => null,
                'headers' => [],
                'error' => $e->getMessage()
            ];
        } catch (Exception $e) {
            // Detect timeout dari error message
            $errorMessage = $e->getMessage();
            if (
                stripos($errorMessage, 'timeout') !== false ||
                stripos($errorMessage, 'timed out') !== false ||
                stripos($errorMessage, 'Maximum execution time') !== false
            ) {
                throw new BpjsTimeoutException();
            }

            return [
                'url' => $url,
                'success' => false,
                'status_code' => 0,
                'data' => null,
                'headers' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Hit API dengan parameter
     *
     * @param string $url
     * @param array $params
     * @param string $method (GET, POST, PUT, DELETE)
     * @param array $headers
     * @param int $timeout
     * @return array
     */
    public static function hitApiWithParams(
        string $url,
        array $params = [],
        string $method = 'GET',
        array $headers = [],
        int $timeout = 30
    ): array {
        try {
            $httpClient = Http::timeout($timeout);

            if (!empty($headers)) {
                $httpClient = $httpClient->withHeaders($headers);
            }

            $response = match (strtoupper($method)) {
                'GET' => $httpClient->get($url, $params),
                'POST' => $httpClient->post($url, $params),
                'PUT' => $httpClient->put($url, $params),
                'DELETE' => $httpClient->delete($url, $params),
                default => $httpClient->get($url, $params)
            };

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'data' => $response->json(),
                'headers' => $response->headers(),
                'error' => null
            ];
        } catch (ConnectionException $e) {
            $errorMessage = $e->getMessage();
            if (
                stripos($errorMessage, 'timeout') !== false ||
                stripos($errorMessage, 'timed out') !== false ||
                stripos($errorMessage, 'Maximum execution time') !== false
            ) {
                throw new BpjsTimeoutException();
            }

            return [
                'success' => false,
                'status_code' => 0,
                'data' => null,
                'headers' => [],
                'error' => $e->getMessage()
            ];
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            if (
                stripos($errorMessage, 'timeout') !== false ||
                stripos($errorMessage, 'timed out') !== false ||
                stripos($errorMessage, 'Maximum execution time') !== false
            ) {
                throw new BpjsTimeoutException();
            }

            return [
                'success' => false,
                'status_code' => 0,
                'data' => null,
                'headers' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Hit API dengan authentication token
     *
     * @param string $url
     * @param array $params
     * @param string $token
     * @param string $method
     * @param string $tokenType (Bearer, Basic, dll)
     * @param int $timeout
     * @return array
     */
    public static function hitApiWithAuth(
        string $url,
        array $params = [],
        string $token = '',
        string $method = 'GET',
        string $tokenType = 'Bearer',
        int $timeout = 30
    ): array {
        try {
            $httpClient = Http::timeout($timeout);

            if (!empty($token)) {
                $httpClient = $httpClient->withToken($token, $tokenType);
            }

            $response = match (strtoupper($method)) {
                'GET' => $httpClient->get($url, $params),
                'POST' => $httpClient->post($url, $params),
                'PUT' => $httpClient->put($url, $params),
                'DELETE' => $httpClient->delete($url, $params),
                default => $httpClient->get($url, $params)
            };

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'data' => $response->json(),
                'headers' => $response->headers(),
                'error' => null
            ];
        } catch (ConnectionException $e) {
            $errorMessage = $e->getMessage();
            if (
                stripos($errorMessage, 'timeout') !== false ||
                stripos($errorMessage, 'timed out') !== false ||
                stripos($errorMessage, 'Maximum execution time') !== false
            ) {
                throw new BpjsTimeoutException();
            }

            return [
                'success' => false,
                'status_code' => 0,
                'data' => null,
                'headers' => [],
                'error' => $e->getMessage()
            ];
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            if (
                stripos($errorMessage, 'timeout') !== false ||
                stripos($errorMessage, 'timed out') !== false ||
                stripos($errorMessage, 'Maximum execution time') !== false
            ) {
                throw new BpjsTimeoutException();
            }

            return [
                'success' => false,
                'status_code' => 0,
                'data' => null,
                'headers' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Hit API dengan file upload
     *
     * @param string $url
     * @param array $params
     * @param array $files ['field_name' => file_path]
     * @param array $headers
     * @param int $timeout
     * @return array
     */
    public static function hitApiWithFiles(
        string $url,
        array $params = [],
        array $files = [],
        array $headers = [],
        int $timeout = 60
    ): array {
        try {
            $httpClient = Http::timeout($timeout);

            if (!empty($headers)) {
                $httpClient = $httpClient->withHeaders($headers);
            }

            foreach ($files as $fieldName => $filePath) {
                if (file_exists($filePath)) {
                    $httpClient = $httpClient->attach($fieldName, file_get_contents($filePath), basename($filePath));
                }
            }

            $response = $httpClient->post($url, $params);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'data' => $response->json(),
                'headers' => $response->headers(),
                'error' => null
            ];
        } catch (ConnectionException $e) {
            $errorMessage = $e->getMessage();
            if (
                stripos($errorMessage, 'timeout') !== false ||
                stripos($errorMessage, 'timed out') !== false ||
                stripos($errorMessage, 'Maximum execution time') !== false
            ) {
                throw new BpjsTimeoutException();
            }

            return [
                'success' => false,
                'status_code' => 0,
                'data' => null,
                'headers' => [],
                'error' => $e->getMessage()
            ];
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            if (
                stripos($errorMessage, 'timeout') !== false ||
                stripos($errorMessage, 'timed out') !== false ||
                stripos($errorMessage, 'Maximum execution time') !== false
            ) {
                throw new BpjsTimeoutException();
            }

            return [
                'success' => false,
                'status_code' => 0,
                'data' => null,
                'headers' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Hit API dengan retry mechanism
     *
     * @param string $url
     * @param array $params
     * @param string $method
     * @param int $maxRetries
     * @param int $retryDelay (seconds)
     * @param array $headers
     * @return array
     */
    public static function hitApiWithRetry(
        string $url,
        array $params = [],
        string $method = 'GET',
        int $maxRetries = 3,
        int $retryDelay = 1,
        array $headers = []
    ): array {
        $lastError = null;

        for ($i = 0; $i <= $maxRetries; $i++) {
            try {
                $httpClient = Http::timeout(30)->retry($maxRetries, $retryDelay * 1000);

                if (!empty($headers)) {
                    $httpClient = $httpClient->withHeaders($headers);
                }

                $response = match (strtoupper($method)) {
                    'GET' => $httpClient->get($url, $params),
                    'POST' => $httpClient->post($url, $params),
                    'PUT' => $httpClient->put($url, $params),
                    'DELETE' => $httpClient->delete($url, $params),
                    default => $httpClient->get($url, $params)
                };

                if ($response->successful()) {
                    return [
                        'success' => true,
                        'status_code' => $response->status(),
                        'data' => $response->json(),
                        'headers' => $response->headers(),
                        'error' => null,
                        'retry_count' => $i
                    ];
                }
            } catch (ConnectionException $e) {
                $lastError = $e->getMessage();
                if (
                    stripos($lastError, 'timeout') !== false ||
                    stripos($lastError, 'timed out') !== false ||
                    stripos($lastError, 'Maximum execution time') !== false
                ) {
                    throw new BpjsTimeoutException();
                }
                if ($i < $maxRetries) {
                    sleep($retryDelay);
                }
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                if (
                    stripos($lastError, 'timeout') !== false ||
                    stripos($lastError, 'timed out') !== false ||
                    stripos($lastError, 'Maximum execution time') !== false
                ) {
                    throw new BpjsTimeoutException();
                }
                if ($i < $maxRetries) {
                    sleep($retryDelay);
                }
            }
        }

        return [
            'success' => false,
            'status_code' => 0,
            'data' => null,
            'headers' => [],
            'error' => $lastError ?: 'Max retries exceeded',
            'retry_count' => $maxRetries
        ];
    }
}
