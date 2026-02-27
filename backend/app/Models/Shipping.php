<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'carrier',
        'tracking_code',
        'status',
        'cost',
        'estimated_days',
        'shipped_at',
        'delivered_at',
        'melhor_envio_quote_id',
        'melhor_envio_service_id',
        'melhor_envio_label_id',
        'from_postal_code',
        'to_postal_code',
        'total_weight',
        'total_volume',
        'package_dimensions',
        'selected_service_name',
        'delivery_time',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'estimated_days' => 'integer',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'melhor_envio_service_id' => 'integer',
        'total_weight' => 'decimal:2',
        'total_volume' => 'decimal:2',
        'package_dimensions' => 'array',
        'delivery_time' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
