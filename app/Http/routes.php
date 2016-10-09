<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/**
 * Simple test to display bot information
 */
Route::get('/', function () {
    $response = Telegram::getMe();
    dd($response);
});

/**
 * Set webhook to allow communication between
 * application and telegram bot.
 */
Route::get('/setWebHook', 'TelegramController@setWebHook');

/**
 * Post route to handle incoming updates from telegram bot
 */
Route::post('/' . config('telegram.bot_token') . '/webhook', 'TelegramController@getWebHook');

/**
 * Remove webhook
 */
Route::get('/unsetWebhook', 'TelegramController@unsetWebHook');

Route::get('/test', 'TelegramController@test');

