<?php


namespace App\Services;


use DMS\Service\Meetup\MeetupKeyAuthClient;
use Illuminate\Support\Collection;

class MeetUpComService
{
    /**
     * @var \DMS\Service\Meetup\MeetupOAuthClient
     */
    protected $client;

    public function __construct()
    {
        $config = [
            'key'    => config('meetupcom.token'),
        ];

        $this->client = MeetupKeyAuthClient::factory($config);
    }

    public function findOpenEvents(array $attributes): Collection
    {
        $locations = $this->client->getOpenEvents($attributes);

        return collect($locations->getData());
    }

}