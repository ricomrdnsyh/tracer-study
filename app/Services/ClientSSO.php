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
