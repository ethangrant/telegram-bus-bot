<?php

namespace App\Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use App\Helpers\TransportApi;
use App\Helpers\CommandHelper;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Chat;
use App\CommandStatus;

class ViewCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "view";

    /**
     * @var string Command Description
     */
    protected $description = "Pass Atcocode/Alias as argument to this command and bus bot will send live departure times to you.";

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

        if(count($arguments) != 1 || empty($arguments[0])) {
            $this->replyWithMessage([
                'text' => "You've supplied too many or too few arguments, try again !",
            ]);

            return;
        }

        $atcocode = $this->commandHelper->getCodeByAlias($arguments[0]);

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

            $text = 'Estimated departure times for ' . $response->stop_name . PHP_EOL . PHP_EOL;

            // Get current time
            $now = new \DateTime();
            $now = $now->setTimeZone(new \DateTimeZone('Europe/London'));

            $estimatedTime = new \DateTime();
            $estimatedTime = $estimatedTime->setTimeZone(new \DateTimeZone('Europe/London'));

            foreach($response->departures as $departures) {
                foreach($departures as $departure) {
                    $timeArray = explode(':', $departure->best_departure_estimate);
                    $estimatedTime = $estimatedTime->setTime($timeArray[0], $timeArray[1]);

                    // Calculate time difference in minutes
                    $interval = $estimatedTime->diff($now);
                    $hours = $interval->format('%h Hours ');
                    $minutes = $interval->format('%i Minutes');

                    $text .= 'Route: ' . $departure->line_name . ' - ' . $hours . $minutes . PHP_EOL;
                }
            }

            $this->replyWithMessage([
                'text' => $text,
            ]);
        }
    }
}