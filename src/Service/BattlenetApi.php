<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BattlenetApi
{

    private ?string $token = null;

    const wowApiUrl = "https://eu.api.blizzard.com";
    const region = "eu";

    public function __construct(
        private readonly string $apiClientId,
        private readonly string $apiClientSecret,
        private readonly HttpClientInterface $httpClient,
    ) {}

    private function getToken(): string
    {
        // TODO: cache + renew
        if ($this->token) return $this->token;
        $response = $this->httpClient->request(
            'POST',
            'https://oauth.battle.net/token',
            [
                'body' => [
                    'grant_type' => 'client_credentials',
                ],
                'auth_basic' => [$this->apiClientId, $this->apiClientSecret],
            ]
        );
        $data = $response->toArray();
        $this->token = $data['access_token'];
        return $this->token;
    }

    public function get(string $uri, ?array $query = [])
    {
        $token = $this->getToken();

        $response = $this->httpClient->request(
            'GET',
            self::wowApiUrl . $uri . '?' . http_build_query($query),
            [
                'headers' => ['Authorization' => 'Bearer ' . $token],
            ]
        );
        return $response->toArray();
    }

}
