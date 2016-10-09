<?php

namespace App\Helpers;

use App\Alias;

class CommandHelper
{
    /**
     * Check to see if supplied argument exists as alias in the alias table.
     * If it does retrieve atcocode that belongs to the alias.
     * If not just assume the user has supplied a atcocode.
     *
     * @param $argument
     * @return mixed
     */
    public function getCodeByAlias($argument)
    {
        $alias = Alias::where('alias', '=', $argument)->get()->first();

        // If null alias does not exist, therefore continue as though using atcocode
        if(is_null($alias)) {
            return $argument;
        }

        return $alias->atcocode;
    }

    /**
     * Simply check if user has supplied correct number of arguments
     *
     * @param $arguments
     * @param $expectedNumber
     * @return bool
     */
    public function checkArgumentsNumber($arguments, $expectedNumber)
    {
        if(count($arguments) != $expectedNumber || empty($arguments[0])) {
            $this->replyWithMessage([
                'text' => "You've supplied too many or too few arguments, try again !",
            ]);

            return true;
        }

        return false;
    }

    /**
     * Returns the current time
     *
     * @return \DateTime
     */
    public function getCurrentTime()
    {
        $now = new \DateTime();
        $now = $now->setTimeZone(new \DateTimeZone('Europe/London'));

        return $now;
    }

    /**
     * Pass in two times to get the difference in minutes
     *
     * @param $currentTime
     * @param $estimatedTime
     * @return mixed
     */
    public function timeDifferenceInMinutes($currentTime, $estimatedTime)
    {
        // Calculate time difference in minutes
        $interval = $estimatedTime->diff($currentTime);
        $minutes = $interval->format('%i Minutes');

        return $minutes;
    }

    /**
     * Pass in two times to get the difference in hours
     *
     * @param $currentTime
     * @param $estimatedTime
     * @return mixed
     */
    public function timeDifferenceInHours($currentTime, $estimatedTime)
    {
        // Calculate time difference in minutes
        $interval = $estimatedTime->diff($currentTime);
        $hours = $interval->format('%h Hours ');

        return $hours;
    }
}