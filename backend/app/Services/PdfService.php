<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    public function generateProposal(Order $order): \Barryvdh\DomPDF\PDF
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
            'location' => 'PdfService.php:14',
            'message' => 'Memory limit before change',
            'data' => [
                'original_limit' => ini_get('memory_limit'),
                'memory_used' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true)
            ],
            'timestamp' => time() * 1000
        ]) . "\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
        // #endregion
        
        // Aumentar limite de memória e tempo de execução para processar PDF com imagem grande
        $originalMemoryLimit = ini_get('memory_limit');
        $originalMaxExecutionTime = ini_get('max_execution_time');
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 300); // 5 minutos
        set_time_limit(300); // Também usar set_time_limit
        
            // #region agent log
            $logEntry = json_encode([
                'runId' => 'run1',
                'hypothesisId' => 'A',
                'location' => 'PdfService.php:35',
                'message' => 'Memory limit after change',
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
            $order->load(['customer', 'status', 'items.product', 'costs.costType']);
            
            // Calcular área total e valor por m²
            $totalArea = 0;
            $totalProductValue = 0;
            $pricePerSquareMeter = 22.00; // Padrão
            
            foreach ($order->items as $item) {
                if ($item->area) {
                    $totalArea += $item->area * ($item->quantity ?? 1);
                }
                $totalProductValue += $item->total_price;
                
                // Pegar preço por m² do produto se disponível
                if ($item->product && $item->product->price_per_square_meter) {
                    $pricePerSquareMeter = $item->product->price_per_square_meter;
                }
            }
            
            // Separar custos
            $installationCost = 0;
            $freightCost = 0;
            $otherCosts = 0;
            
            foreach ($order->costs as $cost) {
                $costName = strtolower($cost->costType->name ?? '');
                $costSlug = strtolower($cost->costType->slug ?? '');
                $value = floatval($cost->value);
                
                if (str_contains($costName, 'instalação') || str_contains($costSlug, 'instalacao') || 
                    str_contains($costName, 'instalacao') || str_contains($costSlug, 'instalacao')) {
                    $installationCost += $value;
                } elseif (str_contains($costName, 'frete') || str_contains($costSlug, 'frete')) {
                    $freightCost += $value;
                } else {
                    // Somar todos os custos (despesas e receitas) ao total
                    $otherCosts += $value;
                }
            }
            
            // Calcular totais
            $totalWithoutInstallation = $totalProductValue + $freightCost + $otherCosts;
            $totalWithInstallation = $totalWithoutInstallation + $installationCost;
            
            // Caminho da imagem de fundo - usar JPG otimizado
            $backgroundImagePath = public_path('NOVO PAPEL TIMBRADO PSIKE DELOUN.jpg');
            $backgroundImagePathForPdf = '';
            
            // #region agent log
            $logEntry = json_encode([
                'runId' => 'run1',
                'hypothesisId' => 'B,C',
                'location' => 'PdfService.php:104',
                'message' => 'Before checking image file',
                'data' => [
                    'image_path' => $backgroundImagePath,
                    'file_exists' => file_exists($backgroundImagePath),
                    'memory_used' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true)
                ],
                'timestamp' => time() * 1000
            ]) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
            // #endregion
            
            if (file_exists($backgroundImagePath)) {
                $backgroundImagePathForPdf = $backgroundImagePath;
                
                // #region agent log
                $fileSize = filesize($backgroundImagePath);
                $imageInfo = @getimagesize($backgroundImagePath);
                $logEntry = json_encode([
                    'runId' => 'run1',
                    'hypothesisId' => 'B,C',
                    'location' => 'PdfService.php:125',
                    'message' => 'Image file info',
                    'data' => [
                        'file_size_bytes' => $fileSize,
                        'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                        'image_width' => $imageInfo[0] ?? null,
                        'image_height' => $imageInfo[1] ?? null,
                        'image_type' => $imageInfo[2] ?? null,
                        'memory_used' => memory_get_usage(true),
                        'memory_peak' => memory_get_peak_usage(true)
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n";
                @file_put_contents($logFile, $logEntry, FILE_APPEND);
                // #endregion
            }
        
            $data = [
                'order' => $order,
                'company' => [
                    'name' => 'Psiké Deloun Arts',
                    'cnpj' => '58.524.955/0001-28',
                    'phone' => '(62) 98219-8202',
                    'email' => 'psikedeloun99@gmail.com',
                    'instagram' => 'psike_deloun',
                ],
                'calculations' => [
                    'totalArea' => $totalArea,
                    'totalProductValue' => $totalProductValue,
                    'pricePerSquareMeter' => $pricePerSquareMeter,
                    'installationCost' => $installationCost,
                    'freightCost' => $freightCost,
                    'otherCosts' => $otherCosts,
                    'totalWithoutInstallation' => $totalWithoutInstallation,
                    'totalWithInstallation' => $totalWithInstallation,
                ],
                'backgroundImagePath' => $backgroundImagePathForPdf,
            ];
            
            // #region agent log
            $logEntry = json_encode([
                'runId' => 'run1',
                'hypothesisId' => 'D',
                'location' => 'PdfService.php:169',
                'message' => 'Before loadView',
                'data' => [
                    'memory_used' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true),
                    'memory_limit' => ini_get('memory_limit'),
                    'data_size' => strlen(serialize($data))
                ],
                'timestamp' => time() * 1000
            ]) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
            // #endregion
            
            // Configurar DomPDF para processar imagens grandes - com try-catch específico
            try {
                // #region agent log
                $startTime = microtime(true);
                $logEntry = json_encode([
                    'runId' => 'run1',
                    'hypothesisId' => 'B,C,D',
                    'location' => 'PdfService.php:186',
                    'message' => 'About to call Pdf::loadView',
                    'data' => [
                        'memory_used' => memory_get_usage(true),
                        'memory_peak' => memory_get_peak_usage(true),
                        'memory_limit' => ini_get('memory_limit'),
                        'max_execution_time' => ini_get('max_execution_time'),
                        'has_background_image' => !empty($backgroundImagePathForPdf),
                        'start_time' => $startTime
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n";
                @file_put_contents($logFile, $logEntry, FILE_APPEND);
                // #endregion
                
                $pdf = Pdf::loadView('pdf.proposal', $data);
                
                // #region agent log
                $loadViewTime = microtime(true) - $startTime;
                $logEntry = json_encode([
                    'runId' => 'run1',
                    'hypothesisId' => 'D',
                    'location' => 'PdfService.php:205',
                    'message' => 'After loadView',
                    'data' => [
                        'load_view_time_seconds' => round($loadViewTime, 2),
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
                    'hypothesisId' => 'D',
                    'location' => 'PdfService.php:200',
                    'message' => 'After loadView, before setOption',
                    'data' => [
                        'memory_used' => memory_get_usage(true),
                        'memory_peak' => memory_get_peak_usage(true),
                        'memory_limit' => ini_get('memory_limit')
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n";
                @file_put_contents($logFile, $logEntry, FILE_APPEND);
                // #endregion
                
                // Configurar opções do DomPDF individualmente
                $pdf->setOption('enable-local-file-access', true);
                
                // #region agent log
                $logEntry = json_encode([
                    'runId' => 'run1',
                    'hypothesisId' => 'B',
                    'location' => 'PdfService.php:212',
                    'message' => 'After setOption enable-local-file-access',
                    'data' => [
                        'memory_used' => memory_get_usage(true),
                        'memory_peak' => memory_get_peak_usage(true)
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n";
                @file_put_contents($logFile, $logEntry, FILE_APPEND);
                // #endregion
                
                $pdf->setOption('isHtml5ParserEnabled', true);
                $pdf->setOption('isRemoteEnabled', false);
                $pdf->setOption('dpi', 96); // Reduzir DPI para economizar memória

                // #region agent log
                $logEntry = json_encode([
                    'runId' => 'run1',
                    'hypothesisId' => 'B,E',
                    'location' => 'PdfService.php:225',
                    'message' => 'After all setOptions, before return',
                    'data' => [
                        'memory_used' => memory_get_usage(true),
                        'memory_peak' => memory_get_peak_usage(true),
                        'memory_limit' => ini_get('memory_limit')
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n";
                @file_put_contents($logFile, $logEntry, FILE_APPEND);
                // #endregion
                
            } catch (\Throwable $dompdfException) {
                // #region agent log
                $logEntry = json_encode([
                    'runId' => 'run1',
                    'hypothesisId' => 'B,C,D',
                    'location' => 'PdfService.php:265',
                    'message' => 'Exception in DomPDF processing',
                    'data' => [
                        'exception_message' => $dompdfException->getMessage(),
                        'exception_file' => $dompdfException->getFile(),
                        'exception_line' => $dompdfException->getLine(),
                        'exception_class' => get_class($dompdfException),
                        'memory_used' => memory_get_usage(true),
                        'memory_peak' => memory_get_peak_usage(true),
                        'memory_limit' => ini_get('memory_limit'),
                        'trace' => substr($dompdfException->getTraceAsString(), 0, 1000) // Primeiros 1000 chars do trace
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n";
                @file_put_contents($logFile, $logEntry, FILE_APPEND);
                // #endregion
                
                // Re-throw para ser capturado pelo catch externo
                throw $dompdfException;
            }
            
            // NÃO restaurar memory_limit aqui - DomPDF ainda não renderizou!
            // O DomPDF faz "lazy rendering" e só renderiza quando download() é chamado
            // PHP resetará automaticamente no fim da requisição
            
            return $pdf;
        } catch (\Exception $e) {
            // #region agent log
            $logFile = '/home/destruidor/psike/.cursor/debug.log';
            $logEntry = json_encode([
                'runId' => 'run1',
                'hypothesisId' => 'A,B,C,D,E',
                'location' => 'PdfService.php:245',
                'message' => 'Exception caught',
                'data' => [
                    'message' => $e->getMessage(),
                    'memory_used' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true),
                    'memory_limit' => ini_get('memory_limit'),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'timestamp' => time() * 1000
            ]) . "\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
            // #endregion
            
            // Não restaurar memory_limit - PHP resetará automaticamente
            \Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}
