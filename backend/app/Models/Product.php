<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'width',
        'height',
        'area',
        'tenda_code',
        'tenda_number',
        'base_id',
        'can_calculate_price',
        'price_per_square_meter',
        'fixed_price',
        'active',
        'box_id',
    ];

    protected $casts = [
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'area' => 'decimal:2',
        'can_calculate_price' => 'boolean',
        'price_per_square_meter' => 'decimal:2',
        'fixed_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name);
            }
            
            if (empty($product->base_id)) {
                $product->base_id = static::generateBaseId($product);
            }
        });

        static::updating(function ($product) {
            // Se o nome mudou e o slug não foi fornecido, gerar novo slug
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name, $product->id);
            }
            
            // Se base_id não foi fornecido, gerar automaticamente
            if (empty($product->base_id)) {
                $product->base_id = static::generateBaseId($product, $product->id);
            }
        });
    }

    /**
     * Gera um slug único a partir do nome
     */
    protected static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        // Verifica se o slug já existe
        while (static::where('slug', $slug)
            ->when($excludeId, function ($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Gera um base_id único baseado na categoria, dimensões e tenda_code
     * Formato: tenda-{CATEGORIA_SLUG}-{width}x{height}-{tenda_code}
     */
    protected static function generateBaseId(Product $product, ?int $excludeId = null): string
    {
        // Carregar categoria se não estiver carregada
        if (!$product->relationLoaded('category') && $product->category_id) {
            $product->load('category');
        }

        // Obter slug da categoria ou usar 'CUSTOM'
        $categorySlug = $product->category?->slug ? strtoupper($product->category->slug) : 'CUSTOM';
        
        // Obter dimensões
        $width = $product->width ?? 0;
        $height = $product->height ?? 0;
        
        // Obter tenda_code ou gerar um padrão
        $tendaCode = $product->tenda_code;
        if (empty($tendaCode)) {
            // Se não houver tenda_code, usar um identificador único baseado no timestamp
            $tendaCode = 'custom-' . time();
        }

        // Gerar base_id base
        $baseId = "tenda-{$categorySlug}-{$width}x{$height}-{$tendaCode}";
        $originalBaseId = $baseId;
        $counter = 1;

        // Verifica se o base_id já existe e gera um único
        while (static::where('base_id', $baseId)
            ->when($excludeId, function ($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists()) {
            $baseId = $originalBaseId . '-' . $counter;
            $counter++;
        }

        return $baseId;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('is_main', 'desc')->orderBy('order');
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class)->orderBy('order');
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function box()
    {
        return $this->belongsTo(Box::class);
    }
}
