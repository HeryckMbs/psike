<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipping;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingController extends Controller
{
    protected $shippingService;

    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    /**
     * Calcula opções de frete
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculate(Request $request)
    {
        // Log detalhado da requisição recebida
        Log::info('Shipping Calculate: Requisição recebida', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'raw_body' => $request->getContent(),
            'all_data' => $request->all(),
            'query_params' => $request->query->all(),
        ]);

        $validated = $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'destination_cep' => 'required|string|min:8|max:10',
            'items' => 'nullable|array',
            'items.*.width' => 'nullable|numeric',
            'items.*.height' => 'nullable|numeric',
            'items.*.quantity' => 'nullable|integer|min:1',
        ]);

        // Log dos dados validados
        Log::info('Shipping Calculate: Dados validados', [
            'validated_data' => $validated,
        ]);

        // Normalizar CEP (remover formatação)
        $destinationCep = preg_replace('/\D/', '', $validated['destination_cep']);
        if (strlen($destinationCep) !== 8) {
            Log::warning('Shipping Calculate: CEP inválido', [
                'destination_cep' => $validated['destination_cep'],
                'normalized_cep' => $destinationCep,
                'cep_length' => strlen($destinationCep),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'CEP inválido',
            ], 400);
        }
        // Formatar CEP para exibição
        $destinationCep = substr($destinationCep, 0, 5) . '-' . substr($destinationCep, 5);
        
        // Se tiver order_id, buscar pedido
        if (isset($validated['order_id'])) {
            Log::info('Shipping Calculate: Buscando pedido', [
                'order_id' => $validated['order_id'],
            ]);
            $order = Order::with('items.product.box')->findOrFail($validated['order_id']);
            
            Log::info('Shipping Calculate: Pedido carregado', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'items_count' => $order->items->count(),
                'items' => $order->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'box_id' => $item->product->box->id ?? null,
                        'box_name' => $item->product->box->name ?? null,
                    ];
                })->toArray(),
            ]);
        } else {
            Log::info('Shipping Calculate: Criando pedido temporário', [
                'items_count' => count($validated['items'] ?? []),
                'items' => $validated['items'] ?? [],
            ]);
            // Criar pedido temporário para cálculo
            $order = new Order();
            $order->setRelation('items', collect($validated['items'] ?? [])->map(function($item) {
                $orderItem = new \App\Models\OrderItem();
                $orderItem->width = $item['width'] ?? null;
                $orderItem->height = $item['height'] ?? null;
                $orderItem->quantity = $item['quantity'] ?? 1;
                return $orderItem;
            }));
        }

        try {
            Log::info('Shipping Calculate: Iniciando cálculo de frete', [
                'order_id' => $order->id ?? null,
                'destination_cep' => $destinationCep,
            ]);

            $options = $this->shippingService->calculateShipping($order, $destinationCep);
            
            Log::info('Shipping Calculate: Cálculo concluído com sucesso', [
                'order_id' => $order->id ?? null,
                'destination_cep' => $destinationCep,
                'options_count' => count($options),
                'options' => $options,
            ]);
            
            return response()->json([
                'success' => true,
                'options' => $options,
            ]);
        } catch (\Exception $e) {
            Log::error('Shipping Calculate: Erro ao calcular frete', [
                'order_id' => $order->id ?? null,
                'destination_cep' => $destinationCep,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
            ]);

            // Se for erro de autenticação, incluir informações adicionais
            $message = $e->getMessage();
            if (strpos($message, 'Token do Melhor Envio') !== false) {
                $message .= ' Para autenticar, acesse: ' . url('/api/melhor-envio/authorize');
            }

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }
    }

    /**
     * Gera etiqueta de envio
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateLabel($id)
    {
        $shipping = Shipping::findOrFail($id);

        try {
            $label = $this->shippingService->generateLabel($shipping);
            
            return response()->json([
                'success' => true,
                'label' => $label,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar etiqueta: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Salva frete selecionado para um pedido
     * 
     * @param Request $request
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveShippingForOrder(Request $request, $orderId)
    {
        $order = Order::with(['items.product.box', 'customer'])->findOrFail($orderId);

        $validated = $request->validate([
            'shipping_option' => 'required|array',
            'shipping_option.id' => 'required|string',
            'shipping_option.price' => 'required|numeric|min:0',
            'shipping_option.delivery_time' => 'nullable|integer',
            'shipping_option.name' => 'nullable|string',
            'shipping_option.company' => 'nullable|string',
            'shipping_option.melhor_envio_service_id' => 'nullable|integer',
        ]);

        try {
            $shippingOption = $validated['shipping_option'];
            
            // Normalizar CEP do cliente
            $customerCep = preg_replace('/\D/', '', $order->customer->cep ?? '');
            if (strlen($customerCep) === 8) {
                $customerCep = substr($customerCep, 0, 5) . '-' . substr($customerCep, 5);
            } else {
                $customerCep = $order->customer->cep ?? '';
            }
            
            // Calcular métricas do pedido
            $metrics = $this->shippingService->calculateOrderMetrics($order);
            
            // Buscar ou criar Shipping
            $shipping = Shipping::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'carrier' => $shippingOption['company'] ?? 'Transportadora',
                    'cost' => $shippingOption['price'],
                    'estimated_days' => $shippingOption['delivery_time'] ?? null,
                    'delivery_time' => $shippingOption['delivery_time'] ?? null,
                    'from_postal_code' => '74000-000',
                    'to_postal_code' => $customerCep,
                    'total_weight' => $metrics['total_weight'],
                    'total_volume' => $metrics['total_volume'],
                    'package_dimensions' => $metrics['package_dimensions'],
                    'selected_service_name' => $shippingOption['name'] ?? null,
                    'melhor_envio_service_id' => $shippingOption['melhor_envio_service_id'] ?? null,
                    'status' => 'pending',
                ]
            );
            
            // Buscar ou criar tipo de custo "Frete"
            $freteCostType = \App\Models\CostType::firstOrCreate(
                ['slug' => 'frete'],
                [
                    'name' => 'Frete',
                    'type' => 'despesa',
                    'active' => true,
                    'order' => 1,
                ]
            );
            
            // Atualizar ou criar OrderCost para frete
            \App\Models\OrderCost::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'cost_type_id' => $freteCostType->id,
                ],
                [
                    'value' => $shippingOption['price'],
                ]
            );
            
            // Recalcular total do pedido
            $totalItems = $order->items->sum('total_price');
            $totalCosts = $order->costs()->where('cost_type_id', '!=', $freteCostType->id)->sum('value');
            $freteCost = $shippingOption['price'];
            
            $order->update([
                'total_amount' => $totalItems + $totalCosts + $freteCost,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Frete salvo com sucesso',
                'shipping' => $shipping->load('order'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar frete: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rastreia pacote
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function track($id)
    {
        $shipping = Shipping::with('order.customer')->findOrFail($id);

        try {
            $tracking = $this->shippingService->trackPackage($shipping);
            
            return response()->json([
                'success' => true,
                'tracking' => $tracking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao rastrear pacote: ' . $e->getMessage(),
            ], 500);
        }
    }
}
