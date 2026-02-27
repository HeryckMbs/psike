<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\OrderCost;
use App\Models\Shipping;
use App\Models\CostType;
use App\Services\ShippingService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'status', 'items.product.images', 'items.product.mainImage', 'items.product.box', 'costs.costType', 'shipping']);

        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer' => 'required|array',
            'customer.name' => 'required|string',
            'customer.document' => 'required|string',
            'customer.phone' => 'required|string',
            'customer.cep' => 'required|string',
            'customer.address' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.product_name' => 'required|string',
            'items.*.width' => 'nullable|numeric',
            'items.*.height' => 'nullable|numeric',
            'items.*.area' => 'nullable|numeric',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric',
            'items.*.total_price' => 'required|numeric',
            'items.*.observations' => 'nullable|string',
            'custom_price' => 'boolean',
            'custom_price_value' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'selected_shipping' => 'nullable|array',
            'selected_shipping.id' => 'nullable|string',
            'selected_shipping.price' => 'nullable|numeric|min:0',
            'selected_shipping.delivery_time' => 'nullable|integer',
            'selected_shipping.name' => 'nullable|string',
            'selected_shipping.company' => 'nullable|string',
            'selected_shipping.melhor_envio_service_id' => 'nullable|integer',
        ]);

        // Criar ou buscar cliente pelo documento
        $customer = Customer::where('document', $validated['customer']['document'])->first();
        
        if ($customer) {
            // Se o cliente já existe, atualizar TODOS os dados com os mais recentes
            $customer->update([
                'name' => $validated['customer']['name'],
                'phone' => $validated['customer']['phone'],
                'cep' => $validated['customer']['cep'],
                'address' => $validated['customer']['address'],
            ]);
        } else {
            // Se não existe, criar novo cliente
            $customer = Customer::create([
                'name' => $validated['customer']['name'],
                'phone' => $validated['customer']['phone'],
                'cep' => $validated['customer']['cep'],
                'address' => $validated['customer']['address'],
                'document' => $validated['customer']['document'],
            ]);
        }

        // Buscar status padrão
        $defaultStatus = OrderStatus::where('is_default', true)->first();
        if (!$defaultStatus) {
            $defaultStatus = OrderStatus::first();
        }

        // Calcular total
        $totalAmount = collect($validated['items'])->sum('total_price');
        if ($validated['custom_price'] ?? false) {
            $totalAmount = $validated['custom_price_value'] ?? $totalAmount;
        }

        // Criar pedido
        $order = Order::create([
            'customer_id' => $customer->id,
            'status_id' => $defaultStatus->id,
            'total_amount' => $totalAmount,
            'custom_price' => $validated['custom_price'] ?? false,
            'custom_price_value' => $validated['custom_price_value'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Criar itens
        foreach ($validated['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'] ?? null,
                'product_name' => $item['product_name'],
                'width' => $item['width'] ?? null,
                'height' => $item['height'] ?? null,
                'area' => $item['area'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price'],
                'observations' => $item['observations'] ?? null,
            ]);
        }

        // Processar frete se selecionado
        $shippingCost = 0;
        
        // Log para debug
        \Log::info('Processando pedido - selected_shipping:', [
            'has_selected_shipping' => isset($validated['selected_shipping']),
            'selected_shipping' => $validated['selected_shipping'] ?? null,
            'is_empty' => empty($validated['selected_shipping'] ?? null),
            'is_array' => is_array($validated['selected_shipping'] ?? null),
        ]);
        
        // Verificar se selected_shipping existe e não está vazio
        $hasShipping = isset($validated['selected_shipping']) 
            && !empty($validated['selected_shipping']) 
            && is_array($validated['selected_shipping'])
            && isset($validated['selected_shipping']['price'])
            && $validated['selected_shipping']['price'] > 0;
        
        if ($hasShipping) {
            $selectedShipping = $validated['selected_shipping'];
            
            \Log::info('Frete encontrado, processando...', [
                'selected_shipping' => $selectedShipping,
            ]);
            $shippingService = new ShippingService();
            
            // Normalizar CEP do cliente
            $customerCep = preg_replace('/\D/', '', $customer->cep);
            if (strlen($customerCep) === 8) {
                $customerCep = substr($customerCep, 0, 5) . '-' . substr($customerCep, 5);
            } else {
                $customerCep = $customer->cep; // Usar como está se não conseguir normalizar
            }
            
            // Recalcular métricas do pedido
            $order->load('items');
            $metrics = $shippingService->calculateOrderMetrics($order);
            
            // Tentar validar/recalcular frete (opcional - pode falhar se token expirou)
            $validatedOption = null;
            try {
                $shippingOptions = $shippingService->calculateShipping($order, $customerCep);
                $validatedOption = collect($shippingOptions)->firstWhere('id', $selectedShipping['id'] ?? '');
            } catch (\Exception $e) {
                // Se falhar, usar dados enviados pelo cliente
                \Log::warning('Erro ao validar frete, usando dados do cliente', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Usar dados validados se disponível, senão usar dados do cliente
            $shippingCost = $validatedOption['price'] ?? $selectedShipping['price'] ?? 0;
            $deliveryTime = $validatedOption['delivery_time'] ?? $selectedShipping['delivery_time'] ?? null;
            $carrier = $validatedOption['company'] ?? $selectedShipping['company'] ?? $selectedShipping['name'] ?? 'Transportadora';
            $serviceName = $validatedOption['name'] ?? $selectedShipping['name'] ?? null;
            $melhorEnvioServiceId = $validatedOption['melhor_envio_service_id'] ?? $selectedShipping['melhor_envio_service_id'] ?? null;
            
            // Criar Shipping com os dados escolhidos pelo cliente
            try {
                $shipping = Shipping::create([
                    'order_id' => $order->id,
                    'carrier' => $carrier,
                    'cost' => $shippingCost,
                    'estimated_days' => $deliveryTime,
                    'delivery_time' => $deliveryTime,
                    'from_postal_code' => '74000-000', // Goiânia - Fixo
                    'to_postal_code' => $customerCep,
                    'total_weight' => $metrics['total_weight'],
                    'total_volume' => $metrics['total_volume'],
                    'package_dimensions' => $metrics['package_dimensions'],
                    'selected_service_name' => $serviceName,
                    'melhor_envio_service_id' => $melhorEnvioServiceId,
                    'status' => 'pending',
                ]);
                
                \Log::info('Shipping criado com sucesso', [
                    'shipping_id' => $shipping->id,
                    'order_id' => $order->id,
                    'cost' => $shippingCost,
                    'carrier' => $carrier,
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar Shipping', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
            
            // Buscar ou criar tipo de custo "Frete"
            $freteCostType = CostType::firstOrCreate(
                ['slug' => 'frete'],
                [
                    'name' => 'Frete',
                    'type' => 'despesa',
                    'active' => true,
                    'order' => 1,
                ]
            );
            
            // Criar OrderCost para frete (usar updateOrCreate para evitar duplicatas)
            try {
                OrderCost::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'cost_type_id' => $freteCostType->id,
                    ],
                    [
                        'value' => $shippingCost,
                    ]
                );
                
                \Log::info('OrderCost de frete criado/atualizado', [
                    'order_id' => $order->id,
                    'cost_value' => $shippingCost,
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao criar OrderCost de frete', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                // Não lançar exceção aqui, apenas logar
            }
            
            // Atualizar total do pedido incluindo frete
            $order->update([
                'total_amount' => $totalAmount + $shippingCost,
            ]);
            
            \Log::info('Frete processado com sucesso', [
                'order_id' => $order->id,
                'shipping_cost' => $shippingCost,
                'carrier' => $carrier,
                'service_name' => $serviceName,
                'total_amount' => $order->total_amount,
            ]);
        } else {
            \Log::warning('Frete não foi selecionado ou está vazio', [
                'has_selected_shipping' => isset($validated['selected_shipping']),
                'selected_shipping' => $validated['selected_shipping'] ?? null,
                'order_id' => $order->id ?? null,
            ]);
        }

        return response()->json($order->load(['customer', 'status', 'items', 'costs.costType', 'shipping']), 201);
    }

    public function show($id)
    {
        $order = Order::with(['customer', 'status', 'items.product.box', 'dimensions', 'costs.costType', 'shipping'])
            ->findOrFail($id);

        return response()->json($order);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status_id' => 'required|exists:order_statuses,id',
        ]);

        $order->update(['status_id' => $validated['status_id']]);

        return response()->json($order->load('status'));
    }

    public function updateDimensions(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'custom_width' => 'required|numeric',
            'custom_height' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $orderItem = $order->items()->findOrFail($validated['order_item_id']);

        $order->dimensions()->create([
            'order_item_id' => $orderItem->id,
            'original_width' => $orderItem->width ?? 0,
            'original_height' => $orderItem->height ?? 0,
            'custom_width' => $validated['custom_width'],
            'custom_height' => $validated['custom_height'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Marcar pedido como custom_price
        $order->update([
            'custom_price' => true,
        ]);

        return response()->json(['message' => 'Dimensions updated']);
    }

    public function updateCustomPrice(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'custom_price_value' => 'required|numeric|min:0',
        ]);

        $order->update([
            'custom_price' => true,
            'custom_price_value' => $validated['custom_price_value'],
        ]);

        return response()->json($order);
    }

    public function generatePdf($id)
    {
        // #region agent log
        $logFile = '/home/destruidor/psike/.cursor/debug.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logEntry = json_encode([
            'runId' => 'run1',
            'hypothesisId' => 'A',
            'location' => 'OrderController.php:180',
            'message' => 'Controller start - memory limit',
            'data' => [
                'original_limit' => ini_get('memory_limit'),
                'memory_used' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true)
            ],
            'timestamp' => time() * 1000
        ]) . "\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
        // #endregion
        
        // Aumentar memória e tempo de execução antes de processar
        $originalMemoryLimit = ini_get('memory_limit');
        $originalMaxExecutionTime = ini_get('max_execution_time');
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 300); // 5 minutos
        set_time_limit(300); // Também usar set_time_limit
        
        // #region agent log
        $logEntry = json_encode([
            'runId' => 'run1',
            'hypothesisId' => 'A',
            'location' => 'OrderController.php:202',
            'message' => 'Controller after ini_set',
            'data' => [
                'new_limit' => ini_get('memory_limit'),
                'memory_used' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true)
            ],
            'timestamp' => time() * 1000
        ]) . "\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
        // #endregion
        try {
            $order = Order::with(['customer', 'status', 'items.product.box', 'costs.costType'])->findOrFail($id);
            
            // #region agent log
            $logEntry = json_encode([
                'runId' => 'run1',
                'hypothesisId' => 'D',
                'location' => 'OrderController.php:213',
                'message' => 'Before calling PdfService',
                'data' => [
                    'memory_used' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true)
                ],
                'timestamp' => time() * 1000
            ]) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
            // #endregion
            
            $pdfService = new \App\Services\PdfService();
            $pdf = $pdfService->generateProposal($order);

            // #region agent log
            $logEntry = json_encode([
                'runId' => 'run1',
                'hypothesisId' => 'B',
                'location' => 'OrderController.php:220',
                'message' => 'After PdfService, before download',
                'data' => [
                    'memory_used' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true)
                ],
                'timestamp' => time() * 1000
            ]) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
            // #endregion
            
            // #region agent log
            $logEntry = json_encode([
                'runId' => 'run1',
                'hypothesisId' => 'B',
                'location' => 'OrderController.php:258',
                'message' => 'Before download() call',
                'data' => [
                    'memory_used' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true),
                    'memory_limit' => ini_get('memory_limit')
                ],
                'timestamp' => time() * 1000
            ]) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
            // #endregion
            
            // NÃO restaurar memory_limit aqui - DomPDF renderiza durante download()!
            // PHP resetará automaticamente no fim da requisição
            
            // Registrar handler para capturar erros fatais durante download
            register_shutdown_function(function() use ($logFile) {
                $error = error_get_last();
                if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                    $logEntry = json_encode([
                        'runId' => 'run1',
                        'hypothesisId' => 'B',
                        'location' => 'OrderController.php:shutdown',
                        'message' => 'Fatal error during PDF generation',
                        'data' => [
                            'error_type' => $error['type'],
                            'error_message' => $error['message'],
                            'error_file' => $error['file'],
                            'error_line' => $error['line'],
                            'memory_used' => memory_get_usage(true),
                            'memory_peak' => memory_get_peak_usage(true),
                            'memory_limit' => ini_get('memory_limit')
                        ],
                        'timestamp' => time() * 1000
                    ]) . "\n";
                    @file_put_contents($logFile, $logEntry, FILE_APPEND);
                }
            });
            
            try {
                // #region agent log
                $logEntry = json_encode([
                    'runId' => 'run1',
                    'hypothesisId' => 'B',
                    'location' => 'OrderController.php:285',
                    'message' => 'About to call download()',
                    'data' => [
                        'memory_used' => memory_get_usage(true),
                        'memory_peak' => memory_get_peak_usage(true)
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n";
                @file_put_contents($logFile, $logEntry, FILE_APPEND);
                // #endregion
                
                return $pdf->download("proposta-{$order->order_number}.pdf");
                
            } catch (\Throwable $downloadException) {
                // #region agent log
                $logEntry = json_encode([
                    'runId' => 'run1',
                    'hypothesisId' => 'B',
                    'location' => 'OrderController.php:298',
                    'message' => 'Exception during download()',
                    'data' => [
                        'exception_message' => $downloadException->getMessage(),
                        'exception_file' => $downloadException->getFile(),
                        'exception_line' => $downloadException->getLine(),
                        'memory_used' => memory_get_usage(true),
                        'memory_peak' => memory_get_peak_usage(true)
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n";
                @file_put_contents($logFile, $logEntry, FILE_APPEND);
                // #endregion
                
                throw $downloadException;
            }
        } catch (\Exception $e) {
            // #region agent log
            $logEntry = json_encode([
                'runId' => 'run1',
                'hypothesisId' => 'A,B',
                'location' => 'OrderController.php:catch',
                'message' => 'Exception in controller',
                'data' => [
                    'exception_message' => $e->getMessage(),
                    'exception_file' => $e->getFile(),
                    'exception_line' => $e->getLine(),
                    'memory_used' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true),
                    'memory_limit' => ini_get('memory_limit')
                ],
                'timestamp' => time() * 1000
            ]) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
            // #endregion
            
            // Não restaurar memory_limit - PHP resetará automaticamente
            \Log::error('Erro ao gerar PDF do pedido ' . $id . ': ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}
