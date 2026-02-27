<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDimension extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'original_width',
        'original_height',
        'custom_width',
        'custom_height',
        'notes',
    ];

    protected $casts = [
        'original_width' => 'decimal:2',
        'original_height' => 'decimal:2',
        'custom_width' => 'decimal:2',
        'custom_height' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
