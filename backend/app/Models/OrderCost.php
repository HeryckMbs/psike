<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'cost_type_id',
        'value',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function costType()
    {
        return $this->belongsTo(CostType::class);
    }
}
