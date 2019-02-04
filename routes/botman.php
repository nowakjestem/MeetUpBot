<?php
use App\Http\Controllers\BotManController;

$botman = resolve('botman');

$botman->hears('location_search', BotManController::class.'@startLocationSearchConversation');
