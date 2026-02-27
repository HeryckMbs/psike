<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'email',
        'phone',
        'document',
        'document_type',
        'cep',
        'address',
        'city',
        'state',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->number)) {
                $lastCustomer = self::orderBy('id', 'desc')->first();
                if ($lastCustomer && $lastCustomer->number) {
                    $lastNumber = str_replace('CLI-', '', $lastCustomer->number);
                    $counter = is_numeric($lastNumber) ? (int) $lastNumber + 1 : 1;
                } else {
                    $counter = 1;
                }
                $customer->number = 'CLI-' . str_pad($counter, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
