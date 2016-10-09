<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use App\Helpers\TransportApi;
use App\Helpers\CommandHelper;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Chat;
use App\CommandStatus;
use Log;

class WarnCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "warn";

    /**
     * @var string Command Description
     */
    protected $description = "Provide an Atcocode/Alias plus a route number and I can warn you when the departure time is less than 5 minutes.";

    /**
     * @var object Command Helper
     */
    protected $commandHelper;

    /**
     * Internal class constructor
     *
     */
    public function __construct()
    {
        $this->commandHelper = new \App\Helpers\CommandHelper();
    }

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $update = $this->getUpdate();
        $chatId = $update->getMessage()->getChat()->getId();
        $chat = Chat::find($chatId);

        $arguments = explode(' ', $arguments);

        if($this->commandHelper->checkArgumentsNumber($arguments, 2)) {
            return;
        }

        $atcocode = $this->commandHelper->getCodeByAlias($arguments[0]);
        $route = $arguments[1];

        $api = new TransportApi(config('transportapi'));

        // Remove any current command status
        CommandStatus::where('chat_id', '=', $chatId)->delete();

        // Check user has ran '/start' at least once so that chat id has been registered
        if($chat->count()) {
            $response = json_decode($api->getLiveTimeTable($atcocode));

            if(isset($response->error)) {
                $this->replyWithMessage([
                    'text' => "There doesn't appear to be a bus stop related to that code.",
                ]);

                return;
            }

            // Get departure time based on route provided
            if(!isset($response->departures->$route)) {
                $this->replyWithMessage([
                    'text' => "The route provided is not available.",
                ]);

                return;
            }

            $departure = $response->departures->$route;
            $timeArray = explode(':', $departure[0]->best_departure_estimate);

            // Get current time
            $now = $this->commandHelper->getCurrentTime();
            $estimatedTime = $this->commandHelper->getCurrentTime();
            $estimatedTime = $estimatedTime->setTime($timeArray[0], $timeArray[1]);

            $hours = $this->commandHelper->timeDifferenceInHours($now, $estimatedTime);
            $minutes = $this->commandHelper->timeDifferenceInMinutes($now, $estimatedTime);

            // Check if the wait is going to be more than an hour. In this case just give up.
            if($hours > 0) {
                $this->replyWithMessage([
                    'text' => 'The next bus will depart in over an hour, warning is currently unavailable.',
                ]);

                return;
            }

            $this->replyWithMessage([
                'text' => 'Watching time table now.',
            ]);

            // Every minute check difference between estimated departure
            // and current time. Quit once the difference is less than
            // five minutes.
            while($minutes > 5) {
                sleep(60);

                // Get an update on the current time.
                $now = $this->commandHelper->getCurrentTime();

                // Calculate time difference in minutes
                $minutes = $this->commandHelper->timeDifferenceInMinutes($now, $estimatedTime);

//                $response = json_decode($api->getLiveTimeTable($atcocode));
                Log::debug('Current time difference in minutes  ' . $minutes . ' Still in loop');
            }

            $text = 'Your bus will depart in less than 5 minutes!';

            $this->replyWithMessage([
                'text' => $text,
            ]);

            return;
        }
    }
}