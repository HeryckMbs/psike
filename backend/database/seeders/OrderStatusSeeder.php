<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderStatus;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Novo', 'slug' => 'new', 'color' => '#1976d2', 'order' => 1, 'is_default' => true],
            ['name' => 'Em Análise', 'slug' => 'review', 'color' => '#ff9800', 'order' => 2],
            ['name' => 'Em Produção', 'slug' => 'production', 'color' => '#2196f3', 'order' => 3],
            ['name' => 'Pronto', 'slug' => 'ready', 'color' => '#4caf50', 'order' => 4],
            ['name' => 'Enviado', 'slug' => 'shipped', 'color' => '#9c27b0', 'order' => 5],
            ['name' => 'Entregue', 'slug' => 'delivered', 'color' => '#00bcd4', 'order' => 6],
            ['name' => 'Cancelado', 'slug' => 'cancelled', 'color' => '#f44336', 'order' => 7],
        ];

        foreach ($statuses as $status) {
            OrderStatus::create($status);
        }
    }
}
