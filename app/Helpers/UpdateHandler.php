<?php

namespace App\Helpers;

use App\Chat;
use App\CommandStatus;
use App\Helpers\TransportApi;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;

class UpdateHandler
{
    /**
     * json decoded telegram update
     *
     * @var $update
     */
    protected $update;

    /**
     * Command status that belongs to current chat user
     *
     * @var \App\CommandStatus
     */
    protected $commandStatus;

    /**
     * Transport api access
     *
     * @var $api
     */
    protected $api;

    /**
     * Internal class constructor
     *
     * @param \App\Helpers\TransportApi $transportApi
     */
    public function __construct(TransportApi $transportApi)
    {
        $this->api = $transportApi;
    }

    /**
     * Handle incoming updates from telegram
     * that aren't of type 'bot_command'
     *
     * @param $update
     */
    public function handle($update)
    {
        $this->update = $this->decodeUpdate($update);

        $chatId = $this->getChatId(json_decode($update));

        if(!is_null(Chat::find($chatId))) {
            $chat = Chat::find($chatId);
            // Check if command status is currently available
            $commandStatus = $chat->commandStatus()->first();

            if(!is_null($commandStatus))
            {
                $commandName = $commandStatus->name;

                // Call function based on command name
                $commandName = str_replace('/', '', $commandName);

                if(is_callable(array($this, $commandName)))
                {
                    $this->commandStatus = $commandStatus;
                    call_user_func(array($this, $commandName));
                    return;
                }
            }
        }

        return;
    }

    /**
     * Replys to the bots request for a current location
     * from the chat that just triggered the '/nearby' command
     *
     * @throws \Exception
     */
    public function nearby()
    {
        if($this->commandStatus->status == 'Awaiting location') {
            // Get coordinates from the user reply
            $latitude = $this->update->message->location->latitude;
            $longitude = $this->update->message->location->longitude;

            $response = json_decode($this->api->getNearbyStops($latitude, $longitude));
            $stops = $response->stops;

            // Loop through stops and send data back to user
            $text = 'Stop name, Atcocode' . PHP_EOL . PHP_EOL;
            foreach($stops as $stop) {
                $text .= $stop->name . ' ' . $stop->indicator . ' - <a>' . $stop->atcocode . '</a>' . PHP_EOL;
            }

            $text .= PHP_EOL . 'The Atcocodes above can be used to find bus times.';

            $response = Telegram::sendMessage([
                'chat_id' => $this->getChatId($this->update),
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

            // Remove the status to indicate command complete
            $this->commandStatus->delete();

            return;
        }

        // Remove the status to indicate command complete
        $this->commandStatus->delete();

        return;
    }

    /**
     * Returns decoded JSON data sent
     * from Telegram updates
     *
     * @param $update
     * @return mixed
     */
    public function decodeUpdate($update)
    {
        return json_decode($update);
    }

    /**
     * Check if the update object is of type 'bot_command'
     *
     * @param $update
     * @return bool
     */
    public function isCommand($update)
    {
        $update = json_decode($update);
        if(isset($update->message->entities[0]->type)) {
            return $update->message->entities[0]->type == 'bot_command' ? true : false;
        }

        return false;
    }

    /**
     * Get chat id from update
     *
     * @param $update
     * @return mixed
     */
    public function getChatId($update)
    {
        return $update->message->chat->id;
    }
}