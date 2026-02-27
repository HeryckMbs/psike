# Integração Melhor Envio - OAuth2

## Configuração

### 1. Variáveis de Ambiente

Adicione as seguintes variáveis no arquivo `.env`:

```env
MELHOR_ENVIO_CLIENT_ID=22703
MELHOR_ENVIO_CLIENT_SECRET=vDeSHyeTTE8zVHQQjrgUaO0ockSFXb74eRXY5m1w
MELHOR_ENVIO_REDIRECT_URI=http://localhost/api/melhor-envio/callback
MELHOR_ENVIO_PRODUCTION=false
MELHOR_ENVIO_USER_AGENT=Psiké Deloun Arts (contato@psikedeloun.com)
```

**Importante sobre User-Agent:**
- O header `User-Agent` é **obrigatório** em todas as requisições à API do Melhor Envio
- Deve seguir o formato: `Nome da Aplicação (email para contato técnico)`
- Este email será usado pelo Melhor Envio para contato em caso de problemas

**Importante:** 
- O `MELHOR_ENVIO_REDIRECT_URI` deve ser **exatamente igual** ao callback configurado no aplicativo no Painel do Melhor Envio
- Para produção, altere `MELHOR_ENVIO_PRODUCTION=true` e use a URL de produção

### 2. Executar Migration

```bash
php artisan migrate
```

Isso criará a tabela `melhor_envio_tokens` para armazenar os tokens de acesso.

## Fluxo de Autenticação

### 1. Iniciar Autorização

Acesse a URL de autorização:

```
GET /api/melhor-envio/authorize?scopes=shipping-calculate shipping-generate&state=optional_state
```

Ou via navegador:
```
http://localhost/api/melhor-envio/authorize
```

Isso redirecionará para a página de autorização do Melhor Envio.

### 2. Callback

Após o usuário autorizar, o Melhor Envio redirecionará para:
```
GET /api/melhor-envio/callback?code=CODE_RETORNADO&state=optional_state
```

O sistema automaticamente:
- Troca o código por token de acesso
- Salva o token no banco de dados
- Desativa tokens anteriores

### 3. Verificar Status

```
GET /api/melhor-envio/status
```

Retorna:
```json
{
  "authenticated": true,
  "token": {
    "id": 1,
    "expires_at": "2026-02-27T10:00:00Z",
    "is_valid": true
  }
}
```

### 4. Revogar Autenticação

```
POST /api/melhor-envio/revoke
```

Desativa todos os tokens ativos.

## Permissões (Scopes)

As permissões padrão incluem:
- `shipping-calculate` - Cotação de fretes
- `shipping-generate` - Geração de etiquetas
- `shipping-tracking` - Rastreamento
- `shipping-print` - Impressão de etiquetas
- `shipping-cancel` - Cancelamento de etiquetas
- `shipping-companies` - Consulta de transportadoras

Para solicitar permissões específicas, passe o parâmetro `scopes` na URL de autorização:
```
/api/melhor-envio/authorize?scopes=shipping-calculate shipping-generate
```

## Uso no Código

### Obter Token Válido

```php
use App\Services\MelhorEnvioAuthService;

$authService = new MelhorEnvioAuthService();
$token = $authService->getValidAccessToken();

if ($token) {
    // Token válido disponível
    // O serviço renova automaticamente se necessário
} else {
    // Nenhum token válido, precisa autorizar novamente
}
```

### Verificar se Está Autenticado

```php
$authService = new MelhorEnvioAuthService();

if ($authService->hasValidToken()) {
    // Sistema está autenticado
}
```

## Integração com ShippingService

O `ShippingService` pode ser atualizado para usar tokens reais do Melhor Envio quando disponíveis. Por enquanto, funciona com dados mockados.

## Troubleshooting

### Erro: "Client invalid"

- Verifique se o `MELHOR_ENVIO_REDIRECT_URI` está **exatamente igual** ao callback configurado no aplicativo
- Verifique se o Client ID e Secret estão corretos

### Token Expirado

O sistema renova automaticamente usando o `refresh_token` quando disponível. Se não conseguir renovar, será necessário autorizar novamente.

### Logs

Os erros são registrados nos logs do Laravel:
```bash
tail -f storage/logs/laravel.log
```
