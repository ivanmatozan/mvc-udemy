<?php

namespace App;

use Mailgun\Mailgun;

/**
 * Class Mail
 */
class Mail
{
    /**
     * Send a message
     *
     * @param string $to Recipient
     * @param string $subject Subject
     * @param string $text Text-only content of the message
     * @param string $html HTML content of the message
     *
     * @return mixed
     */
    public static function send(string $to, string $subject, string $text, string $html)
    {
        $mgClient = new Mailgun(Config::MAILGUN_API_KEY);
        $domain = Config::MAILGUN_DOMAIN;

        $result = $mgClient->sendMessage($domain, array(
            'from'    => 'info@mvc-udemy.com',
            'to'      => $to,
            'subject' => $subject,
            'text'    => $text,
            'html'    => $html
        ));
    }
}