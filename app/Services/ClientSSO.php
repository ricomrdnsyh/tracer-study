<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClientSSO
{
    public function __construct(
        protected AuthSSO $auth
    ) {}

    public function getFakultasFromApi(): array
    {
        return $this->fetchData('fakultas');
    }

    public function getProdiByFakultas(string $idFakultas): array
    {
        return $this->fetchData('program_studi', ['id_fakultas' => $idFakultas]);
    }

    public function getTahunAkademikFromApi(): array
    {
        return $this->fetchData('tahun_ajaran');
    }

    public function getAlumniFromApi(?string $tahunKeluar = null): array
    {
        $payload = [];
        if (!empty($tahunKeluar)) {
            $payload['tahun_keluar'] = $tahunKeluar;
        }

        return $this->fetchAllPaginated('alumni', $payload);
    }

    public function getLembagaFromApi(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget('sso_lembaga_list');
        }

        return Cache::remember('sso_lembaga_list', 86400, function () {
            try {
                return $this->fetchData('lembaga');
            } catch (\Throwable $e) {
                Log::warning("Gagal fetch lembaga SSO: " . $e->getMessage());
                return [];
            }
        });
    }

    public function getKaryawanFromApi(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget('sso_karyawan_list');
        }

        return Cache::remember('sso_karyawan_list', 1800, function () use ($forceRefresh) {
            $allKaryawans = [];

            try {
                $lembagaList = $this->getLembagaFromApi($forceRefresh);

                foreach ($lembagaList as $lembaga) {
                    if (isset($lembaga['id_lembaga'])) {
                        try {
                            $data = $this->fetchData('karyawan', [
                                'id_lembaga' => $lembaga['id_lembaga'],
                                'pagination' => 'off'
                            ]);
                            if (is_array($data)) {
                                $allKaryawans = array_merge($allKaryawans, $data);
                            }
                        } catch (\Throwable $e) {
                            Log::warning("Gagal fetch karyawan lembaga {$lembaga['id_lembaga']}: " . $e->getMessage());
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Gagal fetch karyawan SSO: " . $e->getMessage());
            }

            return $allKaryawans;
        });
    }

    public function fetchAllPaginated(string $filter, array $additionalPayload = []): array
    {
        $auth = $this->auth->getAuth();
        $url = $auth['data_url'];
        $headers = $auth['headers'];

        $payload = array_merge([
            'filter' => $filter,
        ], $additionalPayload);

        $response = Http::withHeaders($headers)
            ->withoutVerifying()
            ->timeout(60)
            ->connectTimeout(15)
            ->post($url . '?page=1', $payload);

        if ($response->status() === 401) {
            $auth = $this->auth->refreshAuth();
            $url = $auth['data_url'];
            $headers = $auth['headers'];

            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->connectTimeout(30)
                ->timeout(120)
                ->post($url . '?page=1', $payload);
        }

        $response->throw();
        $json = $response->json();

        if (isset($json['data']) && is_array($json['data']) && array_is_list($json['data'])) {
            return $json['data'];
        }

        $items = $json['data']['data'] ?? [];
        $lastPage = (int) ($json['data']['last_page'] ?? 1);

        if ($lastPage <= 1) {
            return $items;
        }

        $pageChunks = array_chunk(range(2, $lastPage), 25);
        foreach ($pageChunks as $chunk) {
            $responses = Http::pool(function ($pool) use ($headers, $url, $payload, $chunk) {
                foreach ($chunk as $p) {
                    $pool->withHeaders($headers)
                        ->withoutVerifying()
                        ->timeout(60)
                        ->post($url . '?page=' . $p, $payload);
                }
            });

            foreach ($responses as $r) {
                if ($r instanceof Response && $r->successful()) {
                    $pageItems = $r->json('data.data') ?? [];
                    if (is_array($pageItems)) {
                        $items = array_merge($items, $pageItems);
                    }
                }
            }
        }

        return $items;
    }

    private function fetchData(string $filter, array $additionalPayload = []): array
    {
        $auth = $this->auth->getAuth();

        $url = $auth['data_url'];
        $headers = $auth['headers'];

        $payload = array_merge([
            'filter' => $filter,
            'pagination' => 'off',
        ], $additionalPayload);

        $response = Http::withHeaders($headers)
            ->withoutVerifying()
            ->timeout(60)
            ->connectTimeout(10)
            ->post($url, $payload);

        if ($response->status() === 401) {
            $auth = $this->auth->refreshAuth();
            $url = $auth['data_url'];
            $headers = $auth['headers'];

            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->connectTimeout(30)
                ->timeout(120)
                ->post($url, $payload);
        }

        $response->throw();

        return $response->json('data') ?? [];
    }
}

