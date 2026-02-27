<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Garantir que o path seja uma URL completa se necessário
        $path = $this->path;
        
        // Se o path começa com assets/images, manter como está (será servido pelo frontend)
        if ($path && str_starts_with($path, 'assets/images')) {
            // Manter o path relativo para ser servido pelo frontend
            $path = '/' . ltrim($path, '/');
            // Garantir que o caminho está correto (normalizar)
            $path = str_replace('//', '/', $path);
        }
        // Se o path começa com products/, é uma imagem uploadada e deve usar /storage
        elseif ($path && str_starts_with($path, 'products/')) {
            $path = '/storage/' . $path;
        }
        // Se o path não começa com http ou /storage, adicionar /storage
        elseif ($path && !str_starts_with($path, 'http') && !str_starts_with($path, '/storage') && !str_starts_with($path, '/')) {
            $path = '/storage/' . $path;
        }
        // Se o path começa com / mas não com /storage e não é assets, adicionar /storage
        elseif ($path && str_starts_with($path, '/') && !str_starts_with($path, '/storage') && !str_starts_with($path, '/assets') && !str_starts_with($path, 'http')) {
            $path = '/storage' . $path;
        }
        
        return [
            'id' => $this->id,
            'path' => $path,
            'filename' => $this->filename,
            'is_main' => $this->is_main,
            'variation' => $this->variation,
            'order' => $this->order,
        ];
    }
}
