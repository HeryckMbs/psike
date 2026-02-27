<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MelhorEnvioAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MelhorEnvioAuthController extends Controller
{
    protected $authService;

    public function __construct(MelhorEnvioAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Redireciona para página de autorização do Melhor Envio
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function authorize(Request $request)
    {
        $scopes = $request->input('scopes', []);
        $state = $request->input('state');

        // Se scopes vier como string, converter para array
        if (is_string($scopes)) {
            $scopes = explode(' ', $scopes);
        }

        $authorizationUrl = $this->authService->getAuthorizationUrl($scopes, $state);

        return redirect($authorizationUrl);
    }

    /**
     * Callback após autorização do usuário
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        $code = $request->input('code');
        $state = $request->input('state');
        $error = $request->input('error');

        // Verifica se houve erro na autorização
        if ($error) {
            Log::error('Melhor Envio: Erro na autorização', [
                'error' => $error,
                'error_description' => $request->input('error_description'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro na autorização: ' . ($request->input('error_description') ?? $error),
            ], 400);
        }

        // Verifica se o código foi fornecido
        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Código de autorização não fornecido',
            ], 400);
        }

        // Troca código por token
        $tokenData = $this->authService->exchangeCodeForToken($code);

        if (!$tokenData) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter token de acesso',
            ], 500);
        }

        // Se for requisição AJAX, retorna JSON
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Autenticação realizada com sucesso',
                'data' => $tokenData,
            ]);
        }

        // Caso contrário, redireciona para página de sucesso
        // Você pode ajustar esta URL conforme necessário
        $redirectUrl = config('services.melhor_envio.success_redirect_uri', '/admin/melhor-envio/success');
        
        return redirect($redirectUrl)->with('success', 'Autenticação realizada com sucesso');
    }

    /**
     * Verifica status da autenticação
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        $hasToken = $this->authService->hasValidToken();
        $token = \App\Models\MelhorEnvioToken::getActiveToken();

        return response()->json([
            'authenticated' => $hasToken,
            'token' => $token ? [
                'id' => $token->id,
                'expires_at' => $token->expires_at->toIso8601String(),
                'is_valid' => $token->isValid(),
            ] : null,
        ]);
    }

    /**
     * Revoga autenticação (desativa token)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function revoke()
    {
        \App\Models\MelhorEnvioToken::deactivateAll();

        return response()->json([
            'success' => true,
            'message' => 'Autenticação revogada com sucesso',
        ]);
    }
}
