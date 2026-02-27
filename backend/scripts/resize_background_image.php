<?php

/**
 * Script para redimensionar a imagem de fundo para tamanho A4 (794x1123px a 96 DPI)
 * 
 * Uso: php scripts/resize_background_image.php
 */

$sourceImage = __DIR__ . '/../public/NOVO PAPEL TIMBRADO PSIKE DELOUN.jpg';
$outputImage = __DIR__ . '/../public/NOVO PAPEL TIMBRADO PSIKE DELOUN.jpg';

// Dimensões A4 a 96 DPI
$targetWidth = 794;
$targetHeight = 1123;

if (!file_exists($sourceImage)) {
    die("Erro: Arquivo não encontrado: $sourceImage\n");
}

echo "Carregando imagem: $sourceImage\n";

// Carregar imagem
$image = @imagecreatefromjpeg($sourceImage);
if (!$image) {
    die("Erro: Não foi possível carregar a imagem JPEG. Verifique se o arquivo é válido.\n");
}

// Obter dimensões originais
$originalWidth = imagesx($image);
$originalHeight = imagesy($image);

echo "Dimensões originais: {$originalWidth}x{$originalHeight}px\n";
echo "Dimensões alvo (A4 96 DPI): {$targetWidth}x{$targetHeight}px\n";

// Criar nova imagem com dimensões A4
$newImage = imagecreatetruecolor($targetWidth, $targetHeight);

// Preservar transparência (se houver) e melhorar qualidade
imagealphablending($newImage, false);
imagesavealpha($newImage, false);

// Redimensionar com alta qualidade
imagecopyresampled(
    $newImage,           // Imagem de destino
    $image,              // Imagem de origem
    0, 0,                // X, Y destino
    0, 0,                // X, Y origem
    $targetWidth,        // Largura destino
    $targetHeight,       // Altura destino
    $originalWidth,      // Largura origem
    $originalHeight      // Altura origem
);

// Salvar imagem redimensionada
$quality = 85; // Qualidade JPEG (0-100)
if (imagejpeg($newImage, $outputImage, $quality)) {
    echo "✓ Imagem redimensionada com sucesso!\n";
    echo "Arquivo salvo em: $outputImage\n";
    
    // Verificar tamanho do arquivo
    $fileSize = filesize($outputImage);
    echo "Tamanho do arquivo: " . round($fileSize / 1024, 2) . " KB\n";
} else {
    die("Erro: Não foi possível salvar a imagem redimensionada.\n");
}

// Limpar memória
imagedestroy($image);
imagedestroy($newImage);

echo "\nConcluído!\n";
