<?php

namespace Database\Seeders;

use App\Models\CostType;
use Illuminate\Database\Seeder;

class CostTypeSeeder extends Seeder
{
    public function run(): void
    {
        $costTypes = [
            // Despesas
            [
                'name' => 'Hospedagem',
                'slug' => 'hospedagem',
                'number' => '001',
                'type' => 'despesa',
                'description' => 'Custos relacionados a hospedagem',
                'active' => true,
                'order' => 1,
            ],
            [
                'name' => 'Deslocamento',
                'slug' => 'deslocamento',
                'number' => '002',
                'type' => 'despesa',
                'description' => 'Custos de transporte e deslocamento',
                'active' => true,
                'order' => 2,
            ],
            [
                'name' => 'Alimentação',
                'slug' => 'alimentacao',
                'number' => '003',
                'type' => 'despesa',
                'description' => 'Custos com alimentação',
                'active' => true,
                'order' => 3,
            ],
            [
                'name' => 'Material',
                'slug' => 'material',
                'number' => '004',
                'type' => 'despesa',
                'description' => 'Custos com materiais adicionais',
                'active' => true,
                'order' => 4,
            ],
            [
                'name' => 'Mão de Obra',
                'slug' => 'mao-de-obra',
                'number' => '005',
                'type' => 'despesa',
                'description' => 'Custos com mão de obra extra',
                'active' => true,
                'order' => 5,
            ],
            [
                'name' => 'Equipamento',
                'slug' => 'equipamento',
                'number' => '006',
                'type' => 'despesa',
                'description' => 'Custos com aluguel ou uso de equipamentos',
                'active' => true,
                'order' => 6,
            ],
            // Receitas
            [
                'name' => 'Serviço Adicional',
                'slug' => 'servico-adicional',
                'number' => '101',
                'type' => 'receita',
                'description' => 'Receitas com serviços adicionais',
                'active' => true,
                'order' => 10,
            ],
            [
                'name' => 'Outros',
                'slug' => 'outros',
                'number' => '999',
                'type' => 'despesa',
                'description' => 'Outros custos diversos',
                'active' => true,
                'order' => 99,
            ],
        ];

        foreach ($costTypes as $costType) {
            CostType::updateOrCreate(
                ['slug' => $costType['slug']],
                $costType
            );
        }
    }
}
