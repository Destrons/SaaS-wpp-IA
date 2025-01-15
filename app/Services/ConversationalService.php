<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\GenericNotification;
use App\Notifications\MenuNotification;
use App\Notifications\ScheduleListNotification;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Testing\ClientFake;

class ConversationalService{

    protected User $user;

    protected $client;

    protected array $commands = [
        '!menu' => 'showMenu',
        '!agenda' => 'showSchedule',
        '!insights' => 'showInsights',
        '!update' => 'updateUserTask'

    ];

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function handleIncomingMessage($data)
    {
        $message = $data['Body'];

        if (array_key_exists(strtolower($message), $this->commands)){
            $handler = $this->commands[strtolower($message)];
            return $this->{$handler}();
        }
    }

    public function showMenu()
    {
        $this->user->notify(new MenuNotification());
    }

    public function showSchedule()
    {
        $tasks = $this->user->tasks->where('due_at', '>', now())
        ->sortBy('due_at')
        ->get();

        $this->user->notify(new ScheduleListNotification($tasks, $this->user->name));
    }
}