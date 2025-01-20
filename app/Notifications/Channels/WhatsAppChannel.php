<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;

class WhatsAppChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toWhatsApp($notifiable);
        $to = $notifiable->routeNotificationFor('WhatsApp');
        $from = config('twilio.from');

        $twilio = new Client(config('twilio.account_sid'), config('twilio.auth_token'));

        if ($message->contentSid) {
            return $twilio->messages->create(
                'whatsapp:' . $to,
                [
                    "from" => 'whatsapp:' . $from,
                    "contentSid" => $message->contentSid,
                    "contentVariables" => $message->variables
                ]
            );
        }

        $messages = $this->splitMessage($message->content);
        $sends = [];
        foreach ($messages as $part) {
            $sends[] = $twilio->messages->create(
                'whatsapp:' . $to,
                [
                    'from' => "whatsapp:" . $from,
                    'body' => $part
                ]
            );
        }

        return $sends;

    }
    // Este trecho do codigo tem como objetivo quebrar a mensagem em partes menores a 1600 caracteres, devido a essa limitação. 
    protected function splitMessage($message, $maxLength = 1600){
        $parts =[];

        while (strlen($message) > $maxLength){

            $pos = mb_strrpos(mb_substr($message, 0, $maxLength), ' ');

            if ($pos === false){
                $pos = $maxLength;
            }

            $parts[] = mb_substr($message, 0, $pos);
            $message = mb_substr($message, $pos + 1);
        }

        if (!empty($message)) {
            $parts[] = $message;
        }

        return $parts;
    }
    
}