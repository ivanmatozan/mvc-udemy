<?php

namespace App;

/**
 * Flash notification messages: messages for one-time
 * display using the session for storage between
 */
class Flash
{
    /**
     * Success message type
     * @var string
     */
    const SUCCESS = 'success';

    /**
     * Information message type
     * @var string
     */
    const INFO = 'info';

    /**
     * Warning message type
     * @var string
     */
    const WARNING = 'warning';

    /**
     * Add a message
     *
     * @param string $message
     * @param string $type
     *
     * @return void
     */
    public static function addMessage(string $message, string $type = self::SUCCESS)
    {
        if (!isset($_SESSION['flash_notifications'])) {
            $_SESSION['flash_notifications'] = [];
        }

        // Append the message to the array
        $_SESSION['flash_notifications'][] = [
            'body' => $message,
            'type' => $type
        ];
    }

    /**
     * Get all messages
     *
     * @return mixed An array with all messages or null if none set
     */
    public static function getMessages()
    {
        if (isset($_SESSION['flash_notifications'])) {
            $messages = $_SESSION['flash_notifications'];
            unset($_SESSION['flash_notifications']);

            return $messages;
        }
    }
}