<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'QUADRADAS', 'slug' => 'quadradas', 'order' => 1],
            ['name' => 'RETANGULAR', 'slug' => 'retangular', 'order' => 2],
            ['name' => 'MANDALA', 'slug' => 'mandala', 'order' => 3],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
