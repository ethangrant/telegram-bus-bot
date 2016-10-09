<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use App\Chat;
use App\Alias;
use App\CommandStatus;
use Log;

class AliasesCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "aliases";

    /**
     * @var string Command Description
     */
    protected $description = "Send a list of available aliases.";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $update = $this->getUpdate();
        $chatId = $update->getMessage()->getChat()->getId();
        $chat = Chat::find($chatId);

        // Remove any current command status
        CommandStatus::where('chat_id', '=', $chatId)->delete();

        if($chat->count()) {
            $aliases = $chat->aliases()->get()->all();
            $text = '';

            if(count($aliases)) {
                foreach($aliases as $alias) {
                    $text .= 'Alias: \'' . $alias->alias . '\' - \'' . $alias->atcocode . '\'' . PHP_EOL;
                }

                $this->replyWithMessage([
                    'text' => $text,
                ]);

                return;
            }

            $this->replyWithMessage([
                'text' => 'Looks like no aliases have been set.',
            ]);

        } else {
            $this->replyWithMessage([
                'text' => 'I\'m sorry your chat id has not registered please run \/start.',
            ]);
        }
    }
}