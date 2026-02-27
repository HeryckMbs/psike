<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Dados do catálogo baseados no main.js
        $catalogData = $this->getCatalogData();
        
        foreach ($catalogData as $item) {
            // Buscar categoria
            $category = Category::where('slug', strtolower($item['type']))->first();
            if (!$category) {
                $this->command->warn("Categoria não encontrada: {$item['type']}");
                continue;
            }
            
            // Criar ou atualizar produto
            $product = Product::updateOrCreate(
                ['base_id' => $item['base_id']],
                [
                    'category_id' => $category->id,
                    'name' => "Tenda {$item['width']}m × {$item['height']}m",
                    'slug' => Str::slug("tenda-{$item['width']}x{$item['height']}-{$item['tenda_code']}"),
                    'description' => "Tenda {$item['type']} de {$item['width']}m × {$item['height']}m",
                    'width' => $item['width'],
                    'height' => $item['height'],
                    'area' => $item['area'],
                    'tenda_code' => $item['tenda_code'],
                    'tenda_number' => $item['tenda_number'],
                    'base_id' => $item['base_id'],
                    'can_calculate_price' => $item['type'] !== 'MANDALA',
                    'price_per_square_meter' => 22.00,
                    'fixed_price' => $item['fixed_price'] ?? null,
                    'active' => true,
                ]
            );
            
            // Limpar imagens antigas
            $product->images()->delete();
            
            // Adicionar imagem principal
            if (!empty($item['main_image'])) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $item['main_image'],
                    'filename' => basename($item['main_image']),
                    'is_main' => true,
                    'variation' => null,
                    'order' => 0,
                ]);
            }
            
            // Adicionar variações
            if (!empty($item['variations'])) {
                foreach ($item['variations'] as $index => $variation) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $variation['image'],
                        'filename' => basename($variation['image']),
                        'is_main' => false,
                        'variation' => $variation['variation'],
                        'order' => $index + 1,
                    ]);
                }
            }
        }
        
        $this->command->info('Catálogo migrado com sucesso!');
    }
    
    private function getCatalogData(): array
    {
        // Áreas fixas para MANDALA
        $mandalaFixedAreas = [
            '8x8_tenda1' => 51,
            '11x11_tenda2' => 86,
            '13x13_tenda3' => 129,
            '11x11_tenda4' => 93,
            '14x14_tenda5' => 155,
            '14x14_tenda6' => 155,
            '21x21_tenda7' => 326,
            '23x23_tenda8' => 344,
            '24x24_tenda9' => 460,
            '26x26_tenda10' => 480,
            '33x33_tenda11' => 754,
        ];
        
        // Preços fixos para MANDALA
        $mandalaFixedPrices = [
            '8x8_tenda1' => 1122.00,
            '11x11_tenda2' => 1892.00,
            '13x13_tenda3' => 2838.00,
            '11x11_tenda4' => 2046.00,
            '14x14_tenda5' => 3410.00,
            '14x14_tenda6' => 3410.00,
            '21x21_tenda7' => 7172.00,
            '23x23_tenda8' => 7568.00,
            '24x24_tenda9' => 10120.00,
            '26x26_tenda10' => 10560.00,
            '33x33_tenda11' => 16588.00,
        ];
        
        // Dados do catálogo (baseado no main.js)
        $catalog = [];
        
        // QUADRADAS
        $quadradas = [
            ['width' => 10, 'height' => 10, 'tenda_code' => 'tenda1', 'tenda_number' => 1, 'images' => ['assets/images/CATÁLOGO/QUADRADAS/TENDA 1/10x10_tenda1.png']],
            ['width' => 10, 'height' => 10, 'tenda_code' => 'tenda2', 'tenda_number' => 2, 'images' => ['assets/images/CATÁLOGO/QUADRADAS/TENDA 2/10x10_tenda2.png', 'assets/images/CATÁLOGO/QUADRADAS/TENDA 2/10x10_tenda2(1).png', 'assets/images/CATÁLOGO/QUADRADAS/TENDA 2/10x10_tenda2(2).png', 'assets/images/CATÁLOGO/QUADRADAS/TENDA 2/10x10_tenda2(3).png']],
            ['width' => 10, 'height' => 10, 'tenda_code' => 'tenda3', 'tenda_number' => 3, 'images' => ['assets/images/CATÁLOGO/QUADRADAS/TENDA 3/10x10_tenda3.png', 'assets/images/CATÁLOGO/QUADRADAS/TENDA 3/10x10_tenda3(1).png']],
            ['width' => 10, 'height' => 10, 'tenda_code' => 'tenda4', 'tenda_number' => 4, 'images' => ['assets/images/CATÁLOGO/QUADRADAS/TENDA 4/10x10_tenda4.png', 'assets/images/CATÁLOGO/QUADRADAS/TENDA 4/10x10_tenda4(1).png', 'assets/images/CATÁLOGO/QUADRADAS/TENDA 4/10x10_tenda4(2).png', 'assets/images/CATÁLOGO/QUADRADAS/TENDA 4/10x10_tenda4(3).png', 'assets/images/CATÁLOGO/QUADRADAS/TENDA 4/10x10_tenda4(4).png']],
        ];
        
        foreach ($quadradas as $item) {
            $baseId = "tenda-QUADRADAS-{$item['width']}x{$item['height']}-{$item['tenda_code']}";
            $area = $item['width'] * $item['height'];
            $mainImage = $item['images'][0] ?? null;
            $variations = [];
            
            foreach (array_slice($item['images'], 1) as $index => $img) {
                $variations[] = ['image' => $img, 'variation' => $index + 1];
            }
            
            $catalog[] = [
                'base_id' => $baseId,
                'type' => 'QUADRADAS',
                'width' => $item['width'],
                'height' => $item['height'],
                'area' => $area,
                'tenda_code' => $item['tenda_code'],
                'tenda_number' => $item['tenda_number'],
                'main_image' => $mainImage,
                'variations' => $variations,
                'fixed_price' => null,
            ];
        }
        
        // RETANGULAR (exemplos principais)
        $retangular = [
            ['width' => 4, 'height' => 20, 'tenda_code' => 'tenda6', 'tenda_number' => 6, 'images' => ['assets/images/CATÁLOGO/RETANGULAR/TENDA 6/4x20_tenda6.png', 'assets/images/CATÁLOGO/RETANGULAR/TENDA 6/4x20_tenda6(1).png', 'assets/images/CATÁLOGO/RETANGULAR/TENDA 6/4x20_tenda6(2).png', 'assets/images/CATÁLOGO/RETANGULAR/TENDA 6/4X20_tenda6(3).png']],
            ['width' => 10, 'height' => 30, 'tenda_code' => 'tenda7', 'tenda_number' => 7, 'images' => ['assets/images/CATÁLOGO/RETANGULAR/TENDA 7/10x30_tenda7.png']],
            ['width' => 10, 'height' => 30, 'tenda_code' => 'tenda8', 'tenda_number' => 8, 'images' => ['assets/images/CATÁLOGO/RETANGULAR/TENDA 8/10x30_tenda8.png']],
            ['width' => 7, 'height' => 25, 'tenda_code' => 'tenda9', 'tenda_number' => 9, 'images' => ['assets/images/CATÁLOGO/RETANGULAR/TENDA 9/7x25_tenda9.png', 'assets/images/CATÁLOGO/RETANGULAR/TENDA 9/7X25_tenda9(1).png']],
            ['width' => 8, 'height' => 25, 'tenda_code' => 'tenda10', 'tenda_number' => 10, 'images' => ['assets/images/CATÁLOGO/RETANGULAR/TENDA 10/8x25_tenda10.png', 'assets/images/CATÁLOGO/RETANGULAR/TENDA 10/8x25_tenda10(1).png', 'assets/images/CATÁLOGO/RETANGULAR/TENDA 10/8X25_tenda10(2).png']],
            ['width' => 8, 'height' => 25, 'tenda_code' => 'tenda11', 'tenda_number' => 11, 'images' => ['assets/images/CATÁLOGO/RETANGULAR/TENDA 11/8X25_tenda11.png', 'assets/images/CATÁLOGO/RETANGULAR/TENDA 11/8x25_tenda11(1).png']],
            ['width' => 12, 'height' => 30, 'tenda_code' => 'tenda12', 'tenda_number' => 12, 'images' => ['assets/images/CATÁLOGO/RETANGULAR/TENDA 12/12x30_tenda12.png']],
            ['width' => 12, 'height' => 50, 'tenda_code' => 'tenda13', 'tenda_number' => 13, 'images' => ['assets/images/CATÁLOGO/RETANGULAR/TENDA 13/12x50_tenda13.png']],
            ['width' => 15, 'height' => 50, 'tenda_code' => 'tenda14', 'tenda_number' => 14, 'images' => ['assets/images/CATÁLOGO/RETANGULAR/TENDA 14/15x50_tenda14.png']],
            ['width' => 20, 'height' => 40, 'tenda_code' => 'tenda15', 'tenda_number' => 15, 'images' => ['assets/images/CATÁLOGO/RETANGULAR/TENDA 15/20x40_tenda15.png', 'assets/images/CATÁLOGO/RETANGULAR/TENDA 15/20x40_tenda15(1).png', 'assets/images/CATÁLOGO/RETANGULAR/TENDA 15/20x40_tenda15(2).png']],
            ['width' => 20, 'height' => 40, 'tenda_code' => 'tenda16', 'tenda_number' => 16, 'images' => ['assets/images/CATÁLOGO/RETANGULAR/TENDA 16/20X40_tenda16.png', 'assets/images/CATÁLOGO/RETANGULAR/TENDA 16/20x40_tenda16(1).png']],
        ];
        
        foreach ($retangular as $item) {
            $baseId = "tenda-RETANGULAR-{$item['width']}x{$item['height']}-{$item['tenda_code']}";
            $area = $item['width'] * $item['height'];
            $mainImage = $item['images'][0] ?? null;
            $variations = [];
            
            foreach (array_slice($item['images'], 1) as $index => $img) {
                $variations[] = ['image' => $img, 'variation' => $index + 1];
            }
            
            $catalog[] = [
                'base_id' => $baseId,
                'type' => 'RETANGULAR',
                'width' => $item['width'],
                'height' => $item['height'],
                'area' => $area,
                'tenda_code' => $item['tenda_code'],
                'tenda_number' => $item['tenda_number'],
                'main_image' => $mainImage,
                'variations' => $variations,
                'fixed_price' => null,
            ];
        }
        
        // MANDALA
        $mandala = [
            ['width' => 8, 'height' => 8, 'tenda_code' => 'tenda1', 'tenda_number' => 1, 'area' => 51, 'fixed_price' => 1122.00, 'images' => ['assets/images/CATÁLOGO/MANDALA/TENDA 1/8x8_tenda1.png', 'assets/images/CATÁLOGO/MANDALA/TENDA 1/8x8_tenda1(1).png', 'assets/images/CATÁLOGO/MANDALA/TENDA 1/8x8_tenda1(2).png', 'assets/images/CATÁLOGO/MANDALA/TENDA 1/8x8_tenda1(3).png', 'assets/images/CATÁLOGO/MANDALA/TENDA 1/8x8_tenda1(4).png']],
            ['width' => 11, 'height' => 11, 'tenda_code' => 'tenda2', 'tenda_number' => 2, 'area' => 86, 'fixed_price' => 1892.00, 'images' => ['assets/images/CATÁLOGO/MANDALA/TENDA 2/11x11_tenda2.png', 'assets/images/CATÁLOGO/MANDALA/TENDA 2/11x11_tenda2(1).png', 'assets/images/CATÁLOGO/MANDALA/TENDA 2/11x11_tenda2(2).png', 'assets/images/CATÁLOGO/MANDALA/TENDA 2/11x11_tenda2(3).png', 'assets/images/CATÁLOGO/MANDALA/TENDA 2/11x11_tenda2(4).png']],
            ['width' => 13, 'height' => 13, 'tenda_code' => 'tenda3', 'tenda_number' => 3, 'area' => 129, 'fixed_price' => 2838.00, 'images' => ['assets/images/CATÁLOGO/MANDALA/TENDA 3/13x13_tenda3.png', 'assets/images/CATÁLOGO/MANDALA/TENDA 3/13x13_tenda3(1).png', 'assets/images/CATÁLOGO/MANDALA/TENDA 3/13x13_tenda3(2).png', 'assets/images/CATÁLOGO/MANDALA/TENDA 3/13x13_tenda3(3).png', 'assets/images/CATÁLOGO/MANDALA/TENDA 3/13x13_tenda3(4).png']],
            ['width' => 11, 'height' => 11, 'tenda_code' => 'tenda4', 'tenda_number' => 4, 'area' => 93, 'fixed_price' => 2046.00, 'images' => ['assets/images/CATÁLOGO/MANDALA/TENDA 4/11x11_tenda4.png']],
            ['width' => 14, 'height' => 14, 'tenda_code' => 'tenda5', 'tenda_number' => 5, 'area' => 155, 'fixed_price' => 3410.00, 'images' => ['assets/images/CATÁLOGO/MANDALA/TENDA 5/14x14_tenda5.png', 'assets/images/CATÁLOGO/MANDALA/TENDA 5/14x14_tenda5(1).png', 'assets/images/CATÁLOGO/MANDALA/TENDA 5/14x14_tenda5(2).png']],
            ['width' => 14, 'height' => 14, 'tenda_code' => 'tenda6', 'tenda_number' => 6, 'area' => 155, 'fixed_price' => 3410.00, 'images' => ['assets/images/CATÁLOGO/MANDALA/TENDA 6/14x14_tenda6.png']],
            ['width' => 21, 'height' => 21, 'tenda_code' => 'tenda7', 'tenda_number' => 7, 'area' => 326, 'fixed_price' => 7172.00, 'images' => ['assets/images/CATÁLOGO/MANDALA/TENDA 7/21x21_tenda7.png']],
            ['width' => 23, 'height' => 23, 'tenda_code' => 'tenda8', 'tenda_number' => 8, 'area' => 344, 'fixed_price' => 7568.00, 'images' => ['assets/images/CATÁLOGO/MANDALA/TENDA 8/23x23_tenda8.png', 'assets/images/CATÁLOGO/MANDALA/TENDA 8/23x23_tenda8(1).png']],
            ['width' => 24, 'height' => 24, 'tenda_code' => 'tenda9', 'tenda_number' => 9, 'area' => 460, 'fixed_price' => 10120.00, 'images' => ['assets/images/CATÁLOGO/MANDALA/TENDA 9/24x24_tenda9.png']],
            ['width' => 26, 'height' => 26, 'tenda_code' => 'tenda10', 'tenda_number' => 10, 'area' => 480, 'fixed_price' => 10560.00, 'images' => ['assets/images/CATÁLOGO/MANDALA/TENDA 10/26x26_tenda10.png', 'assets/images/CATÁLOGO/MANDALA/TENDA 10/26x26_tenda10(1).png', 'assets/images/CATÁLOGO/MANDALA/TENDA 10/26X26_tenda10(2).png', 'assets/images/CATÁLOGO/MANDALA/TENDA 10/26x26_tenda10(3).png']],
            ['width' => 33, 'height' => 33, 'tenda_code' => 'tenda11', 'tenda_number' => 11, 'area' => 754, 'fixed_price' => 16588.00, 'images' => ['assets/images/CATÁLOGO/MANDALA/TENDA 11/33x33_tenda11.png']],
        ];
        
        foreach ($mandala as $item) {
            $baseId = "tenda-MANDALA-{$item['width']}x{$item['height']}-{$item['tenda_code']}";
            $mainImage = $item['images'][0] ?? null;
            $variations = [];
            
            foreach (array_slice($item['images'], 1) as $index => $img) {
                $variations[] = ['image' => $img, 'variation' => $index + 1];
            }
            
            $catalog[] = [
                'base_id' => $baseId,
                'type' => 'MANDALA',
                'width' => $item['width'],
                'height' => $item['height'],
                'area' => $item['area'],
                'tenda_code' => $item['tenda_code'],
                'tenda_number' => $item['tenda_number'],
                'main_image' => $mainImage,
                'variations' => $variations,
                'fixed_price' => $item['fixed_price'],
            ];
        }
        
        return $catalog;
    }
}
