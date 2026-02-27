<?php

namespace App\Helpers;

class ShippingHelper
{
    /**
     * Calcula peso, volume e cubagem para malhas tensionadas de tendas
     * 
     * @param float $comprimento Comprimento da tenda em metros
     * @param float $largura Largura da tenda em metros
     * @param float $foldedThickness Espessura média dobrada em metros (padrão: 0.20)
     * @return array Array com peso (kg), volume (m³), cubagem e detalhes
     */
    public static function calculateTentMetrics(
        float $comprimento,
        float $largura,
        float $foldedThickness = 0.20
    ): array {
        // Validações
        if ($comprimento <= 0 || $largura <= 0) {
            return [
                'peso' => 0,
                'volume' => 0,
                'cubagem' => 0,
                'erro' => 'Comprimento e largura devem ser números positivos'
            ];
        }

        // Cálculo de Peso
        // Dividir comprimento e largura por 1.55
        $comprimentoAjustado = $comprimento / 1.55;
        $larguraAjustada = $largura / 1.55;
        
        // Calcular nova área
        $novaArea = $comprimentoAjustado * $larguraAjustada;
        
        // Calcular peso (kg)
        $peso = $novaArea * 0.31;

        // Cálculo de Volume
        // Determinar maior e menor dimensão
        $maiorDimensao = max($comprimento, $largura);
        $menorDimensao = min($comprimento, $largura);
        
        // Comprimento e largura dobrados
        $comprimentoDobrado = $maiorDimensao / 1.55;
        $larguraDobrada = $menorDimensao / 1.55;
        
        // Volume em m³
        $volume = $comprimentoDobrado * $larguraDobrada * $foldedThickness;

        // Cubagem (igual ao volume)
        $cubagem = $volume;

        // Arredondar valores para 2 casas decimais
        return [
            'peso' => round($peso, 2),
            'volume' => round($volume, 2),
            'cubagem' => round($cubagem, 2),
            'detalhes' => [
                'comprimento_ajustado' => round($comprimentoAjustado, 2),
                'largura_ajustada' => round($larguraAjustada, 2),
                'nova_area' => round($novaArea, 2),
                'comprimento_dobrado' => round($comprimentoDobrado, 2),
                'largura_dobrada' => round($larguraDobrada, 2),
                'espessura' => $foldedThickness
            ]
        ];
    }

    /**
     * Estima distância baseada em CEP (região)
     * 
     * @param string $cepOrigem CEP de origem
     * @param string $cepDestino CEP de destino
     * @return int Distância estimada em km
     */
    public static function estimateDistance(string $cepOrigem, string $cepDestino): int
    {
        // Remover formatação
        $cepOrigem = preg_replace('/\D/', '', $cepOrigem);
        $cepDestino = preg_replace('/\D/', '', $cepDestino);
        
        // Extrair região (primeiros 3 dígitos)
        $regiaoOrigem = substr($cepOrigem, 0, 3);
        $regiaoDestino = substr($cepDestino, 0, 3);
        
        // Se mesma região, distância curta
        if ($regiaoOrigem === $regiaoDestino) {
            return rand(50, 200);
        }
        
        // Calcular diferença de região
        $diff = abs((int)$regiaoOrigem - (int)$regiaoDestino);
        
        // Estimar distância baseada na diferença
        if ($diff < 100) {
            return rand(200, 500);
        } elseif ($diff < 500) {
            return rand(500, 1500);
        } else {
            return rand(1500, 3000);
        }
    }

    /**
     * Calcula dimensões de caixa em centímetros baseado nas dimensões da tenda
     * Cada produto vai em sua própria caixa
     * 
     * @param float $comprimento Comprimento da tenda em metros
     * @param float $largura Largura da tenda em metros
     * @return array Array com width, height, length em centímetros
     */
    public static function calculateBoxDimensions(float $comprimento, float $largura): array
    {
        // Calcular dimensões dobradas
        $comprimentoDobrado = $comprimento / 1.55;
        $larguraDobrada = $largura / 1.55;
        
        // Converter para centímetros
        // width = comprimento dobrado em cm
        // length = largura dobrada em cm
        // height = espessura fixa de 20cm (0.20m dobrada)
        $width = max(11, round($comprimentoDobrado * 100)); // Mínimo 11cm
        $height = max(11, round(0.20 * 100)); // 0.20m = 20cm (espessura dobrada)
        $length = max(11, round($larguraDobrada * 100)); // Mínimo 11cm
        
        return [
            'width' => $width,
            'height' => $height,
            'length' => $length,
        ];
    }
}
