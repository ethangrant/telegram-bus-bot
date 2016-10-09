<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Helpers\TransportApi;
use App\Helpers\UpdateHandler;
use Log;

class TelegramController extends Controller
{
    protected $api;

    protected $updateHandler;

    public function __construct(TransportApi $transportApiServiceProvider, UpdateHandler $updateHandler)
    {
        $this->api = $transportApiServiceProvider;
        $this->updateHandler = $updateHandler;
    }

    /**
     * Set Webhook to allow communication between server
     * and Telegram bot
     */
    public function setWebHook()
    {
        $response = Telegram::setWebhook([
            'url' => 'https://ethangrant.co.uk/telegram-bus-bot/public/' . config('telegram.bot_token') . '/webhook',
            'certificate' => '/etc/ssl/certs/nginx-selfsigned.pem'
        ]);
        dd($response);
    }

    /**
     * Remove an existing Webhook
     */
    public function unsetWebHook()
    {
        $response = Telegram::removeWebhook();
        dd($response);
    }

    /**
     * Receive Webhook updates
     */
    public function getWebHook()
    {
        // Returns updates from Telegram bot
        $update = Telegram::getWebhookUpdates();

        if($this->updateHandler->isCommand($update)) {
            // Handles commands sent by the user
            $commandUpdate = Telegram::commandsHandler(true);
        }

        Log::info($update);
        if(!$this->updateHandler->isCommand($update)) {
            // Handle incoming update
            $this->updateHandler->handle($update);
        }
    }

    public function test()
    {
//        $this->api->getLiveTimeTable('0100BRA20443');
        $response = $this->api->getNearbyStops(51.372345, -2.393165);

        dd(json_decode($response));
    }
}