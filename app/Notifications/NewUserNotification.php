<?php

namespace App\Notifications;

use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\Channels\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected string $name/*, protected string $stripelink*/)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WhatsAppChannel::class];
    }
    
    public function toWhatsApp($notification)
    {
        return (new WhatsAppMessage)
            ->contentSid("HX6f820d11fd50a283fc8e0101b6c27d92")
            ->variables([
                "1" => 'name',
                /*"2" => 'stripelink'*/
            ]);
        }
}
