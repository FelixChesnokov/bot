<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

//$router->post('/' . env('TELEGRAM_BOT_TOKEN') . '/webhook', function () {
//    $update = \Telegram\Bot\Laravel\Facades\Telegram::commandsHandler(true);
//
//    // Commands handler method returns an Update object.
//    // So you can further process $update object
//    // to however you want.
//
//    return 'ok';
//});
