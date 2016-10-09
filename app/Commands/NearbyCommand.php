<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use App\Helpers\TransportApi;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Chat;
use App\CommandStatus;
use Log;

class NearbyCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "nearby";

    /**
     * @var string Command Description
     */
    protected $description = "Return nearby bus stops";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $update = $this->getUpdate();
        $decodedUpdate = json_decode($update);
        $chatId = $update->getMessage()->getChat()->getId();
        $chat = Chat::find($chatId);

        // Remove any current command status
        CommandStatus::where('chat_id', '=', $chatId)->delete();

        // Check user has ran '/start' at least once so that chat id has been registered
        if($chat->count()) {
            Log::debug('Chat id has been found!');

            // Prepare and send location request
            $keyboard = [
                [['text' => 'Send my location', 'request_location' => true]],
            ];

            $reply_markup = Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);

            $this->replyWithMessage([
                'text' => 'Send your location so I can find those bus stops!',
                'reply_markup' => $reply_markup
            ]);

            // Update command status for '/nearby' to awaiting reply
            $commandStatus = CommandStatus::firstOrCreate(['name' => '/nearby', 'status' => 'Awaiting location']);
            $chat->commandStatus()->save($commandStatus);
            Log::debug('Command status saved!');
        }
    }
}