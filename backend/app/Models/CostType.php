<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CostType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'active',
        'order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($costType) {
            if (empty($costType->slug)) {
                $costType->slug = Str::slug($costType->name);
            }
        });

        static::updating(function ($costType) {
            if ($costType->isDirty('name') && empty($costType->slug)) {
                $costType->slug = Str::slug($costType->name);
            }
        });
    }

    public function costs()
    {
        return $this->hasMany(OrderCost::class);
    }
}
