<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use App\Chat;
use App\Alias;
use App\CommandStatus;
use Log;

class AliasCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "alias";

    /**
     * @var string Command Description
     */
    protected $description = "Provide an alias for a bus stops Atcocode. '/alias X39 0180BAA01329'";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        // $arguments[0] should be the alias
        // $arguments[1] should be the atcocode
        $arguments = explode(' ', $arguments);

        // Check user has provided two parameters
        if(count($arguments) != 2) {
            $this->replyWithMessage([
                'text' => 'This command requires two parameters \'Alias\' \'Atcocode\'.',
            ]);

            return;
        }

        $update = $this->getUpdate();
        $chatId = $update->getMessage()->getChat()->getId();
        $chat = Chat::find($chatId);

        // Remove any current command status
        CommandStatus::where('chat_id', '=', $chatId)->delete();

        if($chat->count()) {
            // Delete existing alias against provided atcocode
            Alias::where('atcocode', '=', $arguments[1])->delete();

            // Check to see if alias is in use already
            if(Alias::where('alias', '=', $arguments[0])->count()) {
                $this->replyWithMessage([
                    'text' => 'It would seem that alias is already in use.',
                ]);

                return;
            }

            // Insert alias to database
            $alias = Alias::firstOrCreate(['alias' => $arguments[0], 'atcocode' => $arguments[1]]);
            $chat->aliases()->save($alias);

            $this->replyWithMessage([
                'text' => 'I have saved the alias \'' . $arguments[0] . '\' for the stop with a code of ' . $arguments[1] .
                    'Type /aliases to see all of your aliases.',
            ]);
        } else {
            $this->replyWithMessage([
                'text' => 'I\'m sorry your chat id has not registered please run \/start.',
            ]);
        }
    }
}