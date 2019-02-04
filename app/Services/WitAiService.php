<?php

namespace App\Services;


use GuzzleHttp\Client;

class WitAiService
{
    /**
     * @var Client
     */
    protected $guzzleClient;

    /**
     * @var string
     */
    protected $version;

    public function __construct()
    {
        $config = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('witai.token'),
            ],
            'base_uri' => config('witai.api_url'),
        ];
        $this->guzzleClient = new Client($config);
        $this->version = config('witai.version');
    }

    public function processMessage(string $message): array
    {
        $response = $this->guzzleClient->get('message', [
            'query' => [
                'v' => $this->version,
                'q' => strtolower($message),
                'n' => 2
            ],
        ]);

        $entities = array_get(json_decode($response->getBody()->getContents(), true), 'entities');

        $data = array_map(function ($entity) {
            return array_filter($entity, function ($value) {
                return array_get($value, 'confidence', 0) > config('witai.minimal_confidence');
            });
        }, $entities);

        return $data;
    }
}