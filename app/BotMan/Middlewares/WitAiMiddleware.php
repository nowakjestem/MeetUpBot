<?php


namespace App\BotMan\Middlewares;


use App\Services\WitAiService;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Interfaces\Middleware\Received;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

class WitAiMiddleware implements Received
{
    /**
     * @var WitAiService
     */
    protected $witAiService;

    public function __construct(WitAiService $witAiService)
    {
        $this->witAiService = $witAiService;
    }

    /**
     * Handle an incoming message.
     *
     * @param IncomingMessage $message
     * @param callable $next
     * @param BotMan $bot
     *
     * @return mixed
     */
    public function received(IncomingMessage $message, $next, BotMan $bot)
    {
        $data = $this->witAiService->processMessage($message->getText());

        $message->addExtras('entities', $data);
        if ($intent = array_get($data, 'intent.0.value')) {
            $message->setText($intent);
        }

        return $next($message);
    }
}