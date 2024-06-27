<?php

namespace Nobir\MiniWizard\Traits;

trait ConsoleHelper
{
    /**
     * Ask the user for confirmation in the console.
     *
     * @param string $question
     * @param bool $default
     * @return bool
     */
    public static function confirm($question, $default = false)
    {
        $response = readline($question . ' (yes/no) [' . ($default ? 'yes' : 'no') . ']: ');

        if (empty($response)) {
            return $default;
        }

        return strtolower($response) === 'yes';
    }
}
