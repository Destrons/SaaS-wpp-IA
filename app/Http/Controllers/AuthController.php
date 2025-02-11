<?php

namespace App\Http\Controllers;

use App\Services\OAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(protected OAuthService $oauthService) {}

    public function redirectToMicrosoft()
    {
        return $this->oauthService->getLoginUrl();
    }

    public function handleMicrosoftCallback(Request $request)
    {
        $code = $request->query('code');
    
        if (!$code) {
            return redirect('/login')->with('error', 'Código de autenticação não recebido.');
        }
    
        $user = $this->oauthService->handleCallback($code, $request);
    
        if (!$user) {
            return redirect('/login')->with('error', 'Erro ao recuperar dados do usuário.');
        }
    
        Auth::login($user);
        return view('/oauth_callback');
    }

    
    public function logout(Request $request)
    {
        Auth::logout(); // Desloga o usuário
        $request->session()->invalidate(); // Invalida a sessão atual
        $request->session()->regenerateToken(); // Evita ataques CSRF após logout

        return redirect('/')->with('message', 'Você saiu da conta.');
    }
}
