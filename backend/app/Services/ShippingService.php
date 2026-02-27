<?php

namespace App\Services;

use App\Helpers\ShippingHelper;
use App\Models\Order;
use App\Models\Shipping;
use App\Services\MelhorEnvioAuthService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShippingService
{
    private const ORIGIN_CEP = '74475-219'; // Goiânia - Fixo
    private const BASE_URL_SANDBOX = 'https://sandbox.melhorenvio.com.br';
    private const BASE_URL_PRODUCTION = 'https://melhorenvio.com.br';
    
    private $authService;
    private $isProduction;
    private $userAgent;

    public function __construct(MelhorEnvioAuthService $authService = null)
    {
        $this->authService = $authService ?? new MelhorEnvioAuthService();
        $this->isProduction = config('services.melhor_envio.production', false);
        $this->userAgent = config('services.melhor_envio.user_agent', 'Aplicação (contato@exemplo.com)');
    }

    /**
     * Retorna a URL base da API do Melhor Envio
     */
    private function getMelhorEnvioBaseUrl(): string
    {
        return $this->isProduction ? self::BASE_URL_PRODUCTION : self::BASE_URL_SANDBOX;
    }

    /**
     * Calcula peso e volume total do pedido usando caixas padrões dos produtos
     * 
     * @param Order $order
     * @return array Array com total_weight, total_volume e package_dimensions
     * @throws \Exception Se algum produto não tiver caixa associada
     */
    public function calculateOrderMetrics(Order $order): array
    {
        $totalWeight = 0;
        $totalVolume = 0;
        $packageDimensions = [];

        // Carregar produtos com suas caixas
        $order->load('items.product.box');

        foreach ($order->items as $item) {
            $quantity = $item->quantity ?? 1;
            
            // Buscar caixa do produto
            $box = null;
            if ($item->product_id && $item->product) {
                $box = $item->product->box;
            }

            // Validar que o produto tem caixa
            if (!$box) {
                throw new \Exception("Produto '{$item->product_name}' não possui caixa padrão associada. É necessário associar uma caixa ao produto antes de calcular o frete.");
            }

            // Validar que a caixa está ativa
            if (!$box->active) {
                throw new \Exception("A caixa '{$box->name}' associada ao produto '{$item->product_name}' está inativa.");
            }

            // Usar dimensões e peso da caixa
            $itemWeight = floatval($box->weight);
            $itemVolume = (floatval($box->width) * floatval($box->height) * floatval($box->length)) / 1000000; // cm³ para m³
            
            $totalWeight += $itemWeight * $quantity;
            $totalVolume += $itemVolume * $quantity;
            
            // Adicionar dimensões da caixa
            $packageDimensions[] = [
                'weight' => $itemWeight,
                'volume' => $itemVolume,
                'quantity' => $quantity,
                'box_id' => $box->id,
                'box_name' => $box->name,
                'box_width_cm' => floatval($box->width),
                'box_height_cm' => floatval($box->height),
                'box_length_cm' => floatval($box->length),
            ];
        }

        // Garantir valores mínimos
        $totalWeight = max($totalWeight, 0.1);
        $totalVolume = max($totalVolume, 0.001);

        return [
            'total_weight' => round($totalWeight, 2),
            'total_volume' => round($totalVolume, 2),
            'package_dimensions' => $packageDimensions,
        ];
    }

    /**
     * Calcula opções de frete usando APENAS API do Melhor Envio
     * 
     * @param Order $order
     * @param string $destinationCep
     * @return array Array com opções de frete
     * @throws \Exception Se não conseguir calcular com Melhor Envio
     */
    public function calculateShipping(Order $order, string $destinationCep): array
    {
        // Verificar se tem token válido
        if (!$this->authService->hasValidToken()) {
            Log::error('Melhor Envio: Token não encontrado ou inválido');
            
            // Obter URL de autorização para incluir na mensagem
            $authorizeUrl = $this->authService->getAuthorizationUrl([
                'shipping-calculate',
                'shipping-generate',
                'shipping-tracking',
            ]);
            
            throw new \Exception(
                'Token do Melhor Envio não encontrado ou inválido. ' .
                'É necessário autenticar primeiro. ' .
                'Acesse: ' . $authorizeUrl . ' ou use a rota /api/melhor-envio/authorize'
            );
        }

        Log::info('Melhor Envio: Token válido encontrado, usando API do Melhor Envio');
        
        $result = $this->calculateWithMelhorEnvio($order, $destinationCep);
        
        if ($result === false) {
            Log::error('Melhor Envio: Falha ao calcular frete');
            throw new \Exception('Erro ao calcular frete com Melhor Envio. Verifique os logs para mais detalhes.');
        }

        Log::info('Melhor Envio: Retornando resultado da API do Melhor Envio', [
            'options_count' => count($result),
        ]);
        
        return $result;
    }

    /**
     * Calcula frete usando API real do Melhor Envio
     * 
     * @param Order $order
     * @param string $destinationCep
     * @return array|false Array com opções de frete ou false em caso de erro
     */
    public function calculateWithMelhorEnvio(Order $order, string $destinationCep): array|false
    {
        try {
            $token = $this->authService->getValidAccessToken();
            if (!$token) {
                return false;
            }

            // Normalizar CEPs
            $fromCep = preg_replace('/\D/', '', self::ORIGIN_CEP);
            $toCep = preg_replace('/\D/', '', $destinationCep);

            // Carregar produtos com suas caixas
            $order->load('items.product.box');

            // Preparar produtos conforme documentação do Melhor Envio
            // Agrupar produtos iguais com quantity > 1 (não criar um produto por unidade)
            $products = [];
            $totalInsuranceValue = 0;

            foreach ($order->items as $item) {
                // Buscar caixa do produto
                $box = null;
                if ($item->product_id && $item->product) {
                    $box = $item->product->box;
                }

                // Validar que o produto tem caixa
                if (!$box) {
                    Log::error('Melhor Envio: Produto sem caixa', [
                        'item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                    ]);
                    return false;
                }

                // Validar que a caixa está ativa
                if (!$box->active) {
                    Log::error('Melhor Envio: Caixa inativa', [
                        'item_id' => $item->id,
                        'box_id' => $box->id,
                        'box_name' => $box->name,
                    ]);
                    return false;
                }
                
                // Calcular valor segurado (preço unitário do item)
                $insuranceValue = floatval($item->unit_price ?? 0);
                $quantity = intval($item->quantity ?? 1);
                $totalInsuranceValue += $insuranceValue * $quantity;

                // Criar produto conforme documentação:
                // - width, height, length devem ser int32 (inteiros)
                // - weight deve ser float
                // - insurance_value deve ser float (duas casas decimais)
                // - quantity deve ser int32
                // - id deve ser string
                $productData = [
                    'id' => $item->product_id ? "product_{$item->product_id}" : "item_{$item->id}",
                    'width' => intval(round(floatval($box->width))), // cm - int32 conforme documentação
                    'height' => intval(round(floatval($box->height))), // cm - int32 conforme documentação
                    'length' => intval(round(floatval($box->length))), // cm - int32 conforme documentação
                    'weight' => max(0.1, round(floatval($box->weight), 2)), // kg (mínimo 0.1) - float
                    'insurance_value' => round($insuranceValue, 2), // BRL - float com 2 casas decimais
                    'quantity' => $quantity, // int32 - quantidade do item
                ];
                
                $products[] = $productData;
                
                // Log detalhado de cada produto sendo enviado
                Log::info('Melhor Envio: Produto preparado para envio', [
                    'item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'box_id' => $box->id,
                    'box_name' => $box->name,
                    'box_dimensions' => [
                        'width_cm' => floatval($box->width),
                        'height_cm' => floatval($box->height),
                        'length_cm' => floatval($box->length),
                        'weight_kg' => floatval($box->weight),
                    ],
                    'product_data' => $productData,
                    'unit_price' => $insuranceValue,
                    'quantity' => $quantity,
                ]);
            }

            if (empty($products)) {
                return false;
            }

            // Montar requisição
            $payload = [
                'from' => [
                    'postal_code' => $fromCep,
                ],
                'to' => [
                    'postal_code' => $toCep,
                ],
                'products' => $products,
                'options' => [
                    'receipt' => false,
                    'own_hand' => false,
                ],
            ];

            $url = $this->getMelhorEnvioBaseUrl() . '/api/v2/me/shipment/calculate';
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
                'User-Agent' => $this->userAgent,
            ];

            // Log detalhado da requisição ANTES de enviar
            Log::info('Melhor Envio: Iniciando requisição de cálculo de frete', [
                'order_id' => $order->id,
                'url' => $url,
                'method' => 'POST',
                'headers' => [
                    'Accept' => $headers['Accept'],
                    'Content-Type' => $headers['Content-Type'],
                    'Authorization' => 'Bearer ' . substr($token, 0, 10) . '...' . substr($token, -4) . ' (token truncado)',
                    'User-Agent' => $headers['User-Agent'],
                ],
                'payload' => [
                    'from' => $payload['from'],
                    'to' => $payload['to'],
                    'products_count' => count($payload['products']),
                    'products' => $payload['products'],
                    'options' => $payload['options'],
                ],
                'total_insurance_value' => $totalInsuranceValue,
                'environment' => $this->isProduction ? 'production' : 'sandbox',
            ]);

            // Fazer requisição
            $response = Http::withHeaders($headers)->post($url, $payload);

            // Log detalhado da resposta
            $responseStatus = $response->status();
            $responseBody = $response->body();
            $responseHeaders = $response->headers();
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Log de sucesso com detalhes
                Log::info('Melhor Envio: Requisição bem-sucedida', [
                    'order_id' => $order->id,
                    'status_code' => $responseStatus,
                    'response_headers' => $responseHeaders,
                    'response_data' => $data,
                    'services_count' => isset($data['data']) && is_array($data['data']) ? count($data['data']) : 0,
                ]);
                
                // Converter resposta do Melhor Envio para formato padrão
                $options = [];
                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $service) {
                        $options[] = [
                            'id' => (string)($service['id'] ?? ''),
                            'name' => $service['name'] ?? '',
                            'company' => $service['company']['name'] ?? '',
                            'price' => floatval($service['custom_price'] ?? $service['price'] ?? 0),
                            'delivery_time' => intval($service['custom_delivery_time'] ?? $service['delivery_time'] ?? 0),
                            'delivery_range' => $this->formatDeliveryRange($service['delivery_time'] ?? 0),
                            'melhor_envio_service_id' => intval($service['id'] ?? 0),
                            'original_price' => floatval($service['price'] ?? 0),
                            'original_delivery_time' => intval($service['delivery_time'] ?? 0),
                        ];
                    }
                }

                // Ordenar por preço
                usort($options, function($a, $b) {
                    return $a['price'] <=> $b['price'];
                });

                // Log das opções processadas
                Log::info('Melhor Envio: Opções de frete processadas', [
                    'order_id' => $order->id,
                    'options_count' => count($options),
                    'options' => $options,
                ]);

                return $options;
            }

            // Log detalhado de erro
            Log::error('Melhor Envio: Erro ao calcular frete', [
                'order_id' => $order->id,
                'status_code' => $responseStatus,
                'response_headers' => $responseHeaders,
                'response_body' => $responseBody,
                'response_json' => $response->json(),
                'url' => $url,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Melhor Envio: Exceção ao calcular frete', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Calcula opções de frete usando cotação de produção
     * Baseado em peso, volume, distância e quantidade de caixas
     * 
     * @param Order $order
     * @param string $destinationCep
     * @return array Array com opções de frete
     */
    private function calculateProductionQuote(Order $order, string $destinationCep): array
    {
        // Calcular métricas do pedido (usa caixas dos produtos)
        try {
            $metrics = $this->calculateOrderMetrics($order);
        } catch (\Exception $e) {
            Log::error('Erro ao calcular métricas do pedido', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
        
        // Estimar distância em km
        $distance = ShippingHelper::estimateDistance(self::ORIGIN_CEP, $destinationCep);
        
        // Contar número total de caixas
        $totalBoxes = array_sum(array_column($metrics['package_dimensions'], 'quantity'));
        
        // Calcular base de preço baseado em múltiplos fatores
        // Fórmula: (peso * fator_peso) + (volume * fator_volume) + (distância * fator_distância) + (caixas * fator_caixa)
        $weightFactor = 3.5; // R$ por kg
        $volumeFactor = 120; // R$ por m³
        $distanceFactor = 0.18; // R$ por km
        $boxFactor = 5; // R$ por caixa adicional
        
        $basePrice = ($metrics['total_weight'] * $weightFactor) 
                   + ($metrics['total_volume'] * $volumeFactor) 
                   + ($distance * $distanceFactor)
                   + (max(0, $totalBoxes - 1) * $boxFactor); // Primeira caixa já está no cálculo base
        
        // Aplicar margem de segurança e lucro (20%)
        $basePrice = $basePrice * 1.2;
        
        // Garantir valor mínimo e máximo razoáveis
        $basePrice = max(25, min(500, $basePrice));
        
        // Calcular prazo base baseado na distância
        $baseDeliveryDays = max(3, min(15, ceil($distance / 200))); // ~200km por dia útil
        
        // Gerar opções de frete com variações baseadas no tipo de serviço
        $options = [
            [
                'id' => 'correios-pac',
                'name' => 'Correios PAC',
                'company' => 'Correios',
                'price' => round($basePrice * 1.15, 2), // 15% mais caro
                'delivery_time' => $baseDeliveryDays + 2,
                'delivery_range' => $this->formatDeliveryRange($baseDeliveryDays + 2),
                'melhor_envio_service_id' => null,
            ],
            [
                'id' => 'jadlog-package',
                'name' => 'Jadlog Package',
                'company' => 'Jadlog',
                'price' => round($basePrice * 1.0, 2), // Preço base
                'delivery_time' => $baseDeliveryDays,
                'delivery_range' => $this->formatDeliveryRange($baseDeliveryDays),
                'melhor_envio_service_id' => null,
            ],
            [
                'id' => 'azul-cargo',
                'name' => 'Azul Cargo',
                'company' => 'Azul',
                'price' => round($basePrice * 1.25, 2), // 25% mais caro (mais rápido)
                'delivery_time' => max(3, $baseDeliveryDays - 2),
                'delivery_range' => $this->formatDeliveryRange(max(3, $baseDeliveryDays - 2)),
                'melhor_envio_service_id' => null,
            ],
        ];
        
        // Ordenar por preço
        usort($options, function($a, $b) {
            return $a['price'] <=> $b['price'];
        });
        
        return $options;
    }

    /**
     * Formata prazo de entrega em texto legível
     */
    private function formatDeliveryRange(int $days): string
    {
        if ($days <= 0) {
            return 'A consultar';
        }

        if ($days === 1) {
            return '1 dia útil';
        }

        // Calcular range (aproximadamente ±20% do prazo)
        $minDays = max(1, floor($days * 0.8));
        $maxDays = ceil($days * 1.2);

        if ($minDays === $maxDays) {
            return "{$minDays} dias úteis";
        }

        return "{$minDays} a {$maxDays} dias úteis";
    }

    /**
     * Gera etiqueta de envio (mockado)
     * 
     * @param Shipping $shipping
     * @return array Dados da etiqueta gerada
     */
    public function generateLabel(Shipping $shipping): array
    {
        $labelId = 'MOCK-LABEL-' . strtoupper(Str::random(10));
        $trackingCode = 'BR' . strtoupper(Str::random(11)) . 'BR';
        
        // Atualizar shipping com dados da etiqueta
        $shipping->update([
            'melhor_envio_label_id' => $labelId,
            'tracking_code' => $trackingCode,
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);
        
        return [
            'label_id' => $labelId,
            'url' => 'https://melhorenvio.com.br/label/mock-label.pdf',
            'tracking_code' => $trackingCode,
            'status' => 'generated',
        ];
    }

    /**
     * Rastreia pacote (mockado)
     * 
     * @param Shipping $shipping
     * @return array Dados de rastreamento
     */
    public function trackPackage(Shipping $shipping): array
    {
        $trackingCode = $shipping->tracking_code ?? 'BR' . strtoupper(Str::random(11)) . 'BR';
        
        // Eventos mockados baseados no status
        $events = [];
        
        if ($shipping->status === 'shipped' || $shipping->status === 'delivered') {
            $events[] = [
                'date' => $shipping->shipped_at ? $shipping->shipped_at->format('Y-m-d H:i') : now()->subDays(2)->format('Y-m-d H:i'),
                'location' => 'Goiânia/GO',
                'description' => 'Objeto postado',
            ];
            
            $events[] = [
                'date' => now()->subDays(1)->format('Y-m-d H:i'),
                'location' => 'São Paulo/SP',
                'description' => 'Objeto em trânsito',
            ];
        }
        
        if ($shipping->status === 'delivered') {
            $events[] = [
                'date' => $shipping->delivered_at ? $shipping->delivered_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i'),
                'location' => $shipping->order->customer->city ?? 'Destino',
                'description' => 'Objeto entregue',
            ];
        }
        
        return [
            'tracking_code' => $trackingCode,
            'status' => $shipping->status ?? 'in_transit',
            'events' => $events,
        ];
    }
}
