<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CategorySeeder::class,
            OrderStatusSeeder::class,
            PermissionSeeder::class,
            CatalogSeeder::class,
            CostTypeSeeder::class,
        ]);
    }
}
