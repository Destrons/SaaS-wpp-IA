<?php

use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewUserNotification;
use App\Services\ConversationalService;
use App\Notifications\MenuNotification;
use App\Notifications\ScheduleListNotification;
use App\Notifications\GenericNotification;

function generateTwilioSignature($url, $data) {
    $validator = new \Twilio\Security\RequestValidator(config('twilio.auth_token'));
    return $validator->computeSignature($url, $data);
}

test('new message creates user if not exists', function() {
    $phone = "5511967841671";
    $profileName = "Teste User";

    $request = [
        'From' => 'whatsapp:+' . $phone,
        'ProfileName' => $profileName,
        'WaId' => $phone,
        'To' => config('twilio.from'),
        'Body' => 'Olá, quero criar uma nova tarefa' //Devido a erro de "A chave "Body" está ausente nos dados fornecidos." esse dado foi inserido paliativamente.
    ];

    $signature = generateTwilioSignature(config('twilio.new_message_url'), $request);
    $response = $this->withHeaders([
        'X-Twilio-Signature' => $signature,
    ])->postJson('/api/new_message', $request);

    $response->assertStatus(200);
    $this->assertDatabaseHas('users', [
        'phone' => "+" . $phone,
        'name' => $profileName,
    ]);

});

test('onsubscribed user receives payment Link', function(){

    Notification::fake();
    $user = User::factory()->create([
        'phone' => "+5511967841671",
    ]);

    $request = [
        'From' => 'whatsapp:+' . $user->phone,
        'ProfileName' => $user->name,
        'WaId' => str_replace("+", "", $user->phone),
        'To' => config('twilio.from'),
        'Body' => 'Olá, quero criar uma nova tarefa' //Devido a erro de "A chave "Body" está ausente nos dados fornecidos." esse dado foi inserido paliativamente.
    ];

    $signature = generateTwilioSignature(config('twilio.new_message_url'), $request);
    $response = $this->withHeaders([
        'X-Twilio-Signature' => $signature,
    ])->postJson('/api/new_message', $request);

    $response->assertStatus(200);
    Notification::assertSentTo($user, NewUserNotification::class);
});

test('handle menu command', function(){
    Notification::fake();
    $user = User::factory()->create();

    $service = new ConversationalService();
    $service->setUser($user);
    $service->handleIncomingMessage(['Body' => '!menu']);
    Notification::assertSentTo($user, MenuNotification::class);
});

test('handle agenda command', function(){
    Notification::fake();
    $user = User::factory()->create();

    $service = new ConversationalService();
    $service->setUser($user);
    $service->handleIncomingMessage(['Body' => '!agenda']);
    Notification::assertSentTo($user, ScheduleListNotification::class);
});

test('handle insights command', function () {
    Notification::fake();
    $user = User::factory()->create();
    $tasks = Task::factory()->create([
        'user_id' => $user->id,
        'due_at' => now()->addDay()
    ]);

    $service = new ConversationalService();
    $service->setUser($user);
    $service->handleIncomingMessage(['Body' => '!insights']);
    Notification::assertSentTo($user, GenericNotification::class);
});

test('creates tasks successfully', function () {
    $user = User::factory()->create();
    $service = new ConversationalService();
    $service->setUser($user);

    $task = [
        'description' => 'Test Task',
        'due_at' => now()->addDay(),
        'meta' => 'Test Meta',
        'reminder_at' => now()->addHour(5),
    ];

    $task = $service->createUserTask(...$task);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'user_id' => $user->id,
        'description' => 'Test Task',
    ]);
});

test('updates tasks successfully', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $user->id,
        'description' => 'Descrição antiga',
        'due_at' => now()->addDay(),
    ]);

    $updateData = [
        'taskid' => $task->id,
        'description' => 'Descrição atualizada',
        'due_at' => now()->addDay(2),
        'meta' => 'Test Meta atualizada',
        'reminder_at' => now()->addHour(5),
    ];

    $service = new ConversationalService();
    $service->setUser($user);

    $service->updateUserTask(...$updateData);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'user_id' => $user->id,
        'description' => $updateData['description'],
        'meta' => $updateData['meta'],
    ]);

    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
        'description' => 'Descrição antiga',
    ]);
});



