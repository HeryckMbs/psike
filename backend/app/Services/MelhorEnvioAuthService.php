<?php

namespace App\Services;

use App\Models\MelhorEnvioToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MelhorEnvioAuthService
{
    private const BASE_URL_SANDBOX = 'https://sandbox.melhorenvio.com.br';
    private const BASE_URL_PRODUCTION = 'https://melhorenvio.com.br';
    private const OAUTH_AUTHORIZE = '/oauth/authorize';
    private const OAUTH_TOKEN = '/oauth/token';

    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $isProduction;
    private $userAgent;

    public function __construct()
    {
        $this->clientId = config('services.melhor_envio.client_id');
        $this->clientSecret = config('services.melhor_envio.client_secret');
        $this->redirectUri = config('services.melhor_envio.redirect_uri');
        $this->isProduction = config('services.melhor_envio.production', false);
        $this->userAgent = config('services.melhor_envio.user_agent', 'Aplicação (contato@exemplo.com)');
    }

    /**
     * Retorna a URL base da API
     */
    private function getBaseUrl(): string
    {
        return $this->isProduction ? self::BASE_URL_PRODUCTION : self::BASE_URL_SANDBOX;
    }

    /**
     * Gera URL de autorização OAuth2
     * 
     * @param array $scopes Lista de permissões (scopes)
     * @param string|null $state Estado para retorno
     * @return string URL de autorização
     */
    public function getAuthorizationUrl(array $scopes = [], ?string $state = null): string
    {
        $baseUrl = $this->getBaseUrl();
        $scopeString = !empty($scopes) ? implode(' ', $scopes) : $this->getDefaultScopes();
        
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => $scopeString,
        ];

        if ($state) {
            $params['state'] = $state;
        }

        return $baseUrl . self::OAUTH_AUTHORIZE . '?' . http_build_query($params);
    }

    /**
     * Troca o código de autorização por um token de acesso
     * Conforme documentação: https://melhorenvio.com.br/api-docs
     * 
     * @param string $code Código retornado após autorização
     * @return array|false Dados do token ou false em caso de erro
     */
    public function exchangeCodeForToken(string $code)
    {
        $url = $this->getBaseUrl() . self::OAUTH_TOKEN;
        $payload = [
            'grant_type' => 'authorization_code',
            'client_id' => (int) $this->clientId, // Client ID deve ser int32 conforme documentação
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $code,
        ];
        
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => $this->userAgent,
        ];

        // Log detalhado da requisição ANTES de enviar
        Log::info('Melhor Envio OAuth: Solicitação de token (authorization_code)', [
            'url' => $url,
            'method' => 'POST',
            'headers' => $headers,
            'payload' => [
                'grant_type' => $payload['grant_type'],
                'client_id' => $payload['client_id'],
                'client_secret' => substr($payload['client_secret'], 0, 4) . '...' . substr($payload['client_secret'], -4) . ' (truncado)',
                'redirect_uri' => $payload['redirect_uri'],
                'code' => substr($code, 0, 10) . '...' . substr($code, -4) . ' (truncado)',
            ],
            'environment' => $this->isProduction ? 'production' : 'sandbox',
        ]);

        try {
            $response = Http::withHeaders($headers)->post($url, $payload);

            // Log detalhado da resposta
            $responseStatus = $response->status();
            $responseBody = $response->body();
            $responseHeaders = $response->headers();

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Melhor Envio OAuth: Token obtido com sucesso', [
                    'status_code' => $responseStatus,
                    'response_headers' => $responseHeaders,
                    'token_data' => [
                        'token_type' => $data['token_type'] ?? null,
                        'expires_in' => $data['expires_in'] ?? null,
                        'has_access_token' => isset($data['access_token']),
                        'has_refresh_token' => isset($data['refresh_token']),
                        'access_token_preview' => isset($data['access_token']) ? substr($data['access_token'], 0, 10) . '...' : null,
                    ],
                ]);

                return $this->saveToken($data);
            }

            Log::error('Melhor Envio OAuth: Erro ao trocar código por token', [
                'status_code' => $responseStatus,
                'response_headers' => $responseHeaders,
                'response_body' => $responseBody,
                'response_json' => $response->json(),
                'url' => $url,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Melhor Envio OAuth: Exceção ao trocar código por token', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return false;
        }
    }

    /**
     * Renova o token usando refresh_token
     * Conforme documentação: https://melhorenvio.com.br/api-docs
     * 
     * @param string $refreshToken Refresh token
     * @return array|false Dados do novo token ou false em caso de erro
     */
    public function refreshToken(string $refreshToken)
    {
        $url = $this->getBaseUrl() . self::OAUTH_TOKEN;
        $payload = [
            'grant_type' => 'refresh_token',
            'client_id' => (int) $this->clientId, // Client ID deve ser int32 conforme documentação
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
        ];
        
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => $this->userAgent,
        ];

        // Log detalhado da requisição ANTES de enviar
        Log::info('Melhor Envio OAuth: Renovação de token (refresh_token)', [
            'url' => $url,
            'method' => 'POST',
            'headers' => $headers,
            'payload' => [
                'grant_type' => $payload['grant_type'],
                'client_id' => $payload['client_id'],
                'client_secret' => substr($payload['client_secret'], 0, 4) . '...' . substr($payload['client_secret'], -4) . ' (truncado)',
                'refresh_token' => substr($refreshToken, 0, 10) . '...' . substr($refreshToken, -4) . ' (truncado)',
            ],
            'environment' => $this->isProduction ? 'production' : 'sandbox',
        ]);

        try {
            $response = Http::withHeaders($headers)->post($url, $payload);

            // Log detalhado da resposta
            $responseStatus = $response->status();
            $responseBody = $response->body();
            $responseHeaders = $response->headers();

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Melhor Envio OAuth: Token renovado com sucesso', [
                    'status_code' => $responseStatus,
                    'response_headers' => $responseHeaders,
                    'token_data' => [
                        'token_type' => $data['token_type'] ?? null,
                        'expires_in' => $data['expires_in'] ?? null,
                        'has_access_token' => isset($data['access_token']),
                        'has_refresh_token' => isset($data['refresh_token']),
                        'access_token_preview' => isset($data['access_token']) ? substr($data['access_token'], 0, 10) . '...' : null,
                    ],
                ]);

                return $this->saveToken($data);
            }

            Log::error('Melhor Envio OAuth: Erro ao renovar token', [
                'status_code' => $responseStatus,
                'response_headers' => $responseHeaders,
                'response_body' => $responseBody,
                'response_json' => $response->json(),
                'url' => $url,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Melhor Envio OAuth: Exceção ao renovar token', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return false;
        }
    }

    /**
     * Obtém token de acesso válido (renova se necessário)
     * 
     * @return string|null Token de acesso ou null se não disponível
     */
    public function getValidAccessToken(): ?string
    {
        $token = MelhorEnvioToken::getActiveToken();

        if (!$token) {
            return null;
        }

        // Se o token está válido e não está próximo de expirar, retorna
        if ($token->isValid()) {
            // Verifica se está próximo de expirar (menos de 5 minutos)
            $expiresAt = $token->expires_at->copy();
            if ($expiresAt->subMinutes(5)->isFuture()) {
                return $token->access_token;
            }
        }

        // Se tem refresh_token, tenta renovar
        if ($token->refresh_token) {
            $newToken = $this->refreshToken($token->refresh_token);
            if ($newToken) {
                return $newToken['access_token'];
            }
        }

        // Se não conseguiu renovar, desativa o token
        $token->update(['active' => false]);

        return null;
    }

    /**
     * Salva token no banco de dados
     * 
     * @param array $tokenData Dados do token retornado pela API
     * @return array Dados do token salvo
     */
    private function saveToken(array $tokenData): array
    {
        // Desativa tokens anteriores
        MelhorEnvioToken::deactivateAll();

        // Calcula data de expiração (padrão: 30 dias = 2592000 segundos)
        $expiresIn = $tokenData['expires_in'] ?? 2592000;
        $expiresAt = Carbon::now()->addSeconds($expiresIn);

        // Salva novo token
        $token = MelhorEnvioToken::create([
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'expires_in' => $expiresIn,
            'expires_at' => $expiresAt,
            'token_type' => $tokenData['token_type'] ?? 'Bearer',
            'scope' => $tokenData['scope'] ?? null,
            'active' => true,
        ]);

        return [
            'id' => $token->id,
            'access_token' => $token->access_token,
            'refresh_token' => $token->refresh_token,
            'expires_at' => $token->expires_at->toIso8601String(),
        ];
    }

    /**
     * Retorna scopes padrão para integração de frete
     * 
     * @return string
     */
    private function getDefaultScopes(): string
    {
        return implode(' ', [
            'shipping-calculate',
            'shipping-generate',
            'shipping-tracking',
            'shipping-print',
            'shipping-cancel',
            'shipping-companies',
        ]);
    }

    /**
     * Verifica se há token válido configurado
     * 
     * @return bool
     */
    public function hasValidToken(): bool
    {
        $token = MelhorEnvioToken::getActiveToken();
        return $token && $token->isValid() && !$token->isExpiredOrNearExpiry();
    }
}
