<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'width',
        'height',
        'length',
        'weight',
        'active',
    ];

    protected $casts = [
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'length' => 'decimal:2',
        'weight' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Relacionamento com produtos
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope para buscar apenas caixas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
