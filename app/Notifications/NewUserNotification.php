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
    public function __construct(protected string $name, protected string $stripelink)
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
            ->contentSid("HXfcfaf861bd49bef10b1e1f94a7f7947c")
            ->variables([
                "1" => $this->name,
                "2" => $this->stripelink
            ]);
    }
}
