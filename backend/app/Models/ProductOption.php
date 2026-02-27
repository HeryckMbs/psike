<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'type',
        'values',
        'required',
        'order',
    ];

    protected $casts = [
        'values' => 'array',
        'required' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
