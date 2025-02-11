<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\OAuthNotification;
use App\Services\ConversationalService;
use App\Services\OAuthService;
use App\Services\UserServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class WhatsAppController extends Controller
{
    public function __construct(protected UserServices $userServices, protected ConversationalService $conversationalService, protected OAuthService $oauthService) {

    }

    public function new_message(Request $request)
    {
        $phone = "+" . $request->post('WaId');
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            $user = $this->userServices->store($request->all());
            session(['whatsapp_phone' => $phone]); // Armazena telefone na sessão
            session()->save();
            \Log::info("Número de telefone salvo na sessão: " . session('whatsapp_phone')); // Log para depuração
            return redirect($this->oauthService->getLoginUrl());
        }

        if (!$user->is_authenticated) {
            session(['whatsapp_phone' => $phone]); // Garante que o telefone esteja salvo
            session()->save();
            \Log::info("Número de telefone salvo antes do login Microsoft: " . session('whatsapp_phone'));

            $user->notify(new OAuthNotification($user->name, $this->oauthService->getLoginUrl()));
            return response()->json(['message' => 'Autenticação necessária. Verifique seu WhatsApp para fazer login.'], 401);
        }else{
        // Usuário autenticado, segue o fluxo normal
        $user->last_whatsapp_at = now();
        $user->save();

        $this->conversationalService->setUser($user);
        $this->conversationalService->handleIncomingMessage($request->all());

        return response()->json(['message' => 'Mensagem processada com sucesso.']);
        }
    }
}
