<?php


namespace App\BotMan\Conversations;


use App\Services\MeetUpComService;
use BotMan\BotMan\Messages\Conversations\Conversation;
use Carbon\Carbon;

class LocationSearchConversation extends Conversation
{
    /**
     * @var \App\Services\MeetUpComService
     */
    protected $meetUpComService;

    public function __construct(MeetUpComService $meetUpComService)
    {
        $this->meetUpComService = $meetUpComService;
    }

    public function run(): Conversation
    {
        $arguments = array_merge(
            $this->formatLocation(),
            $this->formatDateTime(),
            $this->formatTopic()
        );

        if (array_has($arguments, 'lat') || array_has($arguments, 'text')) {
            $locations = $this->meetUpComService->findOpenEvents($arguments);
            if ($locations->count()) {
                $text = '';
                $locations->each(function ($location) use (&$text) {
                    $text .= $location['name'] . '; link: ' . $location['event_url'] . "\n";
                });
                return $this->say($text);
            }
            return $this->say('Nie znalazłem żadnych wydarzeń');
        }

        return $this->say('Musisz mi podać jakieś szczegóły :/ Podaj temat lub lokalizację');
    }

    protected function getEntities(): array
    {
        $message = $this->getBot()->getMessage();

        return $message->getExtras('entities');
    }

    protected function formatLocation(): array
    {
        $entities = $this->getEntities();
        $locationString = array_get($entities, 'location.0.value');
        if ($locationString === null) {
            return [];
        }

        /** @var \Geocoder\Provider\OpenCage\Model\OpenCageAddress $location */
        $location = app('geocoder')->geocode($locationString)->get()[0];

        /** @var \Geocoder\Model\Coordinates $coords */
        $coords = $location->getCoordinates();
        return [
            'lat' => $coords->getLatitude(),
            'lon' => $coords->getLongitude(),
            'radius' => 'smart',
        ];
    }

    protected function formatDateTime(): array
    {
        $entities = $this->getEntities();
        if (array_get($entities, 'datetime.0.to') && array_get($entities, 'datetime.0.from')) {
            $from = new Carbon(array_get($entities, 'datetime.0.from.value'));
            $to = new Carbon(array_get($entities, 'datetime.0.to.value'));
            return [
                'time' => "{$from->timestamp}000,{$to->timestamp}000",
            ];
        } elseif (array_get($entities, 'datetime.0.value')) {
            $value = new Carbon(array_get($entities, 'datetime.0.value'));
            return [
                'time' => "{$value->timestamp}000,{$value->endOfDay()->timestamp}000",
            ];
        } else {
            return [];
        }
    }

    protected function formatTopic(): array
    {
        $entities = $this->getEntities();
        return [
            'text' => array_get($entities, 'topic.0.value', ''),
        ];
    }
}