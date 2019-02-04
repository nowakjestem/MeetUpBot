<?php

namespace App\Http\Controllers;

use App\BotMan\Conversations\LocationSearchConversation;
use App\BotMan\Middlewares\WitAiMiddleware;
use BotMan\BotMan\BotMan;
use Illuminate\Http\Request;
use App\Conversations\ExampleConversation;

class BotManController extends Controller
{
    /**
     * Place your BotMan logic here.
     */
    public function handle()
    {
        $botman = app('botman');
        $witAiMiddleware = app(WitAiMiddleware::class);
        $botman->middleware->received($witAiMiddleware);

        $botman->listen();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tinker()
    {
        return view('tinker');
    }

    /**
     * Loaded through routes/botman.php
     * @param  BotMan $bot
     */
    public function startLocationSearchConversation(BotMan $bot)
    {
        $bot->startConversation(app(LocationSearchConversation::class));
    }
}
