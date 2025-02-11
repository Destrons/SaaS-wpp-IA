<?php

namespace App\Notifications;

use App\Notifications\Channels\WhatsAppChannel;
use App\Notifications\Channels\WhatsAppMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OAuthNotification extends Notification
{
    use Queueable;

    protected string $name;
    protected string $authLink;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $name, string $authLink)
    {
        $this->name = $name;
        $this->authLink = $authLink;
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
            ->contentSid("HX7913fc2834a2aab2509a694b291a696f")
            ->variables([
                "1" => $this->name,
                "2" => $this->authLink // Agora enviamos o link de login
            ]);
    }
}
