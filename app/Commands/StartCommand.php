<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use App\Chat;
use Log;

class StartCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "start";

    /**
     * @var string Command Description
     */
    protected $description = "Start Command to get you started";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $update = $this->getUpdate();
        $decodedUpdate = json_decode($update);
        $chatId = $update->getMessage()->getChat()->getId();
        $firstName = $decodedUpdate->message->chat->first_name;
        $lastName = $decodedUpdate->message->chat->last_name;

        Log::info('Chat details ' . $chatId . ' ' . $firstName . ' ' . $lastName);

        $this->replyWithMessage(['text' => "Hello and welcome! I am the bus bot, type '/help' to see a list of available commands."]);

        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $this->triggerCommand('subscribe');

        // Register user and chat id
        $chat = Chat::firstOrCreate(['id' => $chatId, 'first_name' => $firstName, 'last_name' => $lastName]);
    }
}