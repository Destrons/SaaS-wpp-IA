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

        $now = now();
        $messages = [
            ["role" => "user", "content" => "Aja como um assistente pessoal, hoje é $now, se for necessário faça mais perguntas para poder entender melhor a situação"],
            ["role" => "user", "content" => $message],
        ];

        $this->talkToGpt($messages);
    }

    public function showMenu()
    {
        $this->user->notify(new MenuNotification());
    }

    public function showSchedule()
    {
        $tasks = $this->user->tasks()->where('due_at', '>', now())
        ->orderBy('due_at')
        ->get();

        $this->user->notify(new ScheduleListNotification($tasks, $this->user->name));
    }

    public function talkToGpt($messages, $clearMemory= false)
    {
        $client = \OpenAI::client(config('openai.auth_token'));

        $result = $client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => $messages,
            'functions' => [
                [
                    'name' => 'createUserTask',
                    'description' => 'Cria uma tarefa para o usuario',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'description' => [
                                'type' => 'string',
                                'description' => 'Nome da tarefa solicitada pelo usuario'
                            ],
                            'due_at' => [
                                'type' => 'string',
                                'description' => 'Data e hora da tarefa soicitada pelo usuário no formato Y-m-d H:i:s'
                            ],
                            'meta' => [
                                'type' => 'string',
                                'description' => 'Metadados da tarefa solicitada pelo usuario que o chatgpt ache interessante para posteriormente gerar  insights sobre a rotina do usuario. Ex: reuniao de negocios: Discussao de projetos'
                            ],
                            'reminder_at' => [
                                'type' => 'string',
                                'description' => 'Data e hora do lembrete da tarefa em si no formato Y-m-d H:i:s'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->user->notify(new GenericNotification($result->choices[0]->message->content));

    }
}