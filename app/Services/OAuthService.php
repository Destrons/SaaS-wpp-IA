<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class OAuthService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;
    protected string $tenant;
    protected string $authUrl;
    protected string $tokenUrl;
    protected string $graphUrl;

    public function __construct()
    {
        $this->clientId = config('services.microsoft.client_id');
        $this->clientSecret = config('services.microsoft.client_secret');
        $this->redirectUri = config('services.microsoft.redirect_uri');
        $this->tenant = config('services.microsoft.tenant_id');
        $this->authUrl = "https://login.microsoftonline.com/{$this->tenant}/oauth2/v2.0/authorize";
        $this->tokenUrl = "https://login.microsoftonline.com/{$this->tenant}/oauth2/v2.0/token";
        $this->graphUrl = "https://graph.microsoft.com/v1.0/me";
    }

    /**
     * Gera a URL de login do Microsoft OAuth2.0
     */
    public function getLoginUrl(): string
    {
        $sessionId = session()->getId(); // Obtém o Session ID atual
        return "" . http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'response_mode' => 'query',
            'scope' => 'openid profile email',
        ]);
    }

    /**
     * Troca o código de autorização pelo token de acesso
     */
    public function getAccessToken(string $code): ?string
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
        ]);

        if ($response->failed()) {
            Log::error('Falha ao obter token OAuth: ' . $response->body());
            return null;
        }

        return $response->json()['access_token'] ?? null;
    }

    /**
     * Busca os dados do usuário autenticado no Microsoft Graph API
     */
    public function getUserData(string $accessToken): ?array
    {
        $response = Http::withToken($accessToken)->get($this->graphUrl);

        if ($response->failed()) {
            Log::error('Falha ao obter dados do usuário: ' . $response->body());
            return null;
        }

        return $response->json();
    }

    /**
     * Autentica o usuário com OAuth2.0
     */
    public function authenticate(User $user)
    {
        // Gera a URL de login e redireciona o usuário
        return redirect()->away($this->getLoginUrl());
    }

    /**
     * Processa o callback de autenticação e vincula a conta ao banco de dados
     */
    public function handleCallback(string $code, Request $request): ?User
    {

        $accessToken = $this->getAccessToken($code);
        if (!$accessToken) {
            return null;
        }
    
        $userData = $this->getUserData($accessToken);
        if (!$userData) {
            return null;
        }
        
        $sessionId = $request->query('session_id'); // Obtém o session_id da URL
        \Log::info("Session ID recebido do OAuth: " . $sessionId);

        if ($sessionId) {
            session()->setId($sessionId); // 🔥 Restaura a sessão antiga
            session()->start();
        }
    
        $phone = session('whatsapp_phone'); // 🔥 Agora a sessão deve conter o telefone
        \Log::info("Número de telefone recuperado: " . json_encode($phone));
            
        // Se não houver telefone, retorna erro para evitar null no banco
        if (!$phone) {
            \Log::error("Erro: número de telefone do WhatsApp não encontrado na sessão.");
            return null;
        }
    
        // Cria ou atualiza o usuário com o telefone do WhatsApp
        return User::updateOrCreate(
            ['email' => $userData['mail'] ?? $userData['userPrincipalName']],
            [
                'name' => $userData['displayName'],
                'microsoft_id' => $userData['id'],
                'phone' => $phone, // Garante que o telefone do WhatsApp é vinculado
                'is_authenticated' => true,
            ]
        );
    }
}
