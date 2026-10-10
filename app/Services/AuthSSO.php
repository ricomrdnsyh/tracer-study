<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AuthSSO
{
    protected string $cacheKey = 'sso_auth';

    private string $authUrl;

    private string $newBase;

    private string $XToken;

    private string $devId;

    public function __construct()
    {
        $this->authUrl = (string) (config('services.sso.authorize_url') ?: env('SSO_AUTHORIZE_URL'));
        $this->newBase = (string) (config('services.sso.data_url') ?: env('SSO_DATA_URL'));
        $this->XToken = (string) (config('services.sso.x_token') ?: env('SSO_X_TOKEN'));
        $this->devId = (string) (config('services.sso.dev_id') ?: env('SSO_DEV_ID'));
    }

    public function getAuth(): array
    {
        $cached = Cache::get($this->cacheKey);

        if ($cached && isset($cached['data_url'], $cached['headers'], $cached['expired_at'])) {
            $exp = $cached['expired_at'];
            if (is_numeric($exp)) {
                if (now()->timestamp < $exp) {
                    return $cached;
                }
            } elseif ($exp instanceof CarbonInterface) {
                if (now()->lessThan($exp)) {
                    return $cached;
                }
            }
        }

        return $this->refreshAuth();
    }

    public function refreshAuth(): array
    {
        $payload = [
            'X-Token' => $this->XToken,
            'dev_id' => $this->devId,
        ];

        $curlOptions = [];

        if (config('services.sso.force_ipv4', true)) {
            $curlOptions[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
        }

        if (config('services.sso.force_http_1_1', true)) {
            $curlOptions[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
        }
        
        // Tambahan opsi untuk bypass TLS/Cipher errors (cURL error 35)
        $curlOptions[CURLOPT_SSLVERSION] = 6; // CURL_SSLVERSION_TLSv1_2
        $curlOptions[CURLOPT_SSL_CIPHER_LIST] = 'DEFAULT@SECLEVEL=1';

        $response = Http::withoutVerifying()
            ->withOptions(['curl' => $curlOptions])
            ->connectTimeout(10)
            ->timeout(30)
            ->post($this->authUrl, $payload);

        if (! $response->successful()) {
            throw new \Exception(
                'Gagal authorize ke SSO (status '.$response->status().'): '.$response->body()
            );
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new \Exception('Response authorize bukan JSON yang valid.');
        }

        $dataUrl = data_get($json, 'data.info.urls.data');
        $tokenHeader = data_get($json, 'data.token_header', []);

        if (! $dataUrl || empty($tokenHeader['X-Token'])) {
            throw new \Exception('Data URL atau X-Token tidak ditemukan di response authorize.');
        }

        $path = parse_url($dataUrl, PHP_URL_PATH);
        $tokenPart = $path ? basename($path) : null;

        $dataUrl = $tokenPart
            ? rtrim($this->newBase, '/').'/'.$tokenPart
            : $this->newBase;

        $expiredAt = now()->addHours(6);

        $authData = [
            'data_url' => $dataUrl,
            'headers' => $tokenHeader,
            'created_at' => now()->timestamp,
            'expired_at' => $expiredAt->timestamp,
        ];

        Cache::put($this->cacheKey, $authData, $expiredAt);

        return $authData;
    }
}
