<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    protected function formatImagePath($path)
    {
        if (!$path) {
            return null;
        }
        
        // Se o path começa com assets/images, manter como está (será servido pelo frontend)
        if (str_starts_with($path, 'assets/images')) {
            return '/' . ltrim($path, '/');
        }
        // Se o path começa com products/, é uma imagem uploadada e deve usar /storage
        elseif (str_starts_with($path, 'products/')) {
            return '/storage/' . $path;
        }
        // Se o path não começa com http ou /storage, adicionar /storage
        elseif (!str_starts_with($path, 'http') && !str_starts_with($path, '/storage') && !str_starts_with($path, '/')) {
            return '/storage/' . $path;
        }
        // Se o path começa com / mas não com /storage e não é assets, adicionar /storage
        elseif (str_starts_with($path, '/') && !str_starts_with($path, '/storage') && !str_starts_with($path, '/assets') && !str_starts_with($path, 'http')) {
            return '/storage' . $path;
        }
        
        return $path;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'base_id' => $this->base_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'width' => (float) $this->width,
            'height' => (float) $this->height,
            'area' => $this->area ? (float) $this->area : ($this->width * $this->height),
            'tenda_code' => $this->tenda_code,
            'tenda_number' => $this->tenda_number,
            'type' => $this->category->slug ?? null,
            'can_calculate_price' => $this->can_calculate_price,
            'price_per_square_meter' => (float) $this->price_per_square_meter,
            'fixed_price' => $this->fixed_price ? (float) $this->fixed_price : null,
            'active' => (bool) $this->active,
            'category_id' => $this->category_id,
            'box_id' => $this->box_id,
            'main_image' => $this->formatImagePath($this->mainImage?->path ?? ($this->images->first()?->path ?? null)),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'variations' => ProductImageResource::collection($this->images->where('variation', '!=', null)),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'box' => $this->whenLoaded('box'),
            'options' => ProductOptionResource::collection($this->whenLoaded('options')),
            'prices' => ProductPriceResource::collection($this->whenLoaded('prices')),
        ];
    }
}
