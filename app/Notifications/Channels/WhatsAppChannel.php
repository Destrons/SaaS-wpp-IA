<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;

class WhatsAppChannel{

    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toWhatsApp($notifiable);
        $to = $notifiable->routeNotificationFor('WhatsApp');
        $from = config('twilio.from');

        $twilio = new Client(config('twilio.account_sid'), config('twilio.auth_token'));
        
        if($message->contentSid){
            return $twilio->messages->create(
                'whatsapp:' . $to,
                [
                    'from' => 'whatsapp:' . $from,
                    'contentSid' => $message->contentSid,
                    'contentVariables' => $message->variables
                ]
            );
        }
    
        return $twilio->messages->create(
            'whatsapp:' . $to,
            [
                'from' => $from,
                'body' => $message->content
            ]
        );
    }
    
}