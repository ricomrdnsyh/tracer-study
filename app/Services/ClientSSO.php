<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

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

    public function getLembagaFromApi(): array
    {
        return $this->fetchData('lembaga');
    }

    public function getKaryawanFromApi(): array
    {
        $allKaryawans = [];
        
        try {
            $lembagaList = $this->getLembagaFromApi();
            
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
                    } catch (\Exception $e) {
                        // Lanjut ke lembaga berikutnya jika error
                    }
                }
            }
        } catch (\Exception $e) {
            // Gagal fetch lembaga list
        }

        return $allKaryawans;
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

        // Ambil halaman sisanya secara paralel menggunakan Http::pool (chunk 25 halaman per batch)
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

        /** @var Response $response */
        $response = Http::withHeaders($headers)
            ->withoutVerifying()
            ->timeout(60)
            ->connectTimeout(10)
            ->post($url, $payload);

        if ($response->status() === 401) {
            $auth = $this->auth->refreshAuth();
            $url = $auth['data_url'];
            $headers = $auth['headers'];

            /** @var Response $response */
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
