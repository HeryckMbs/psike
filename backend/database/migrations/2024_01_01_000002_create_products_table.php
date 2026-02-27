<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('width', 8, 2); // Largura em metros
            $table->decimal('height', 8, 2); // Altura/Comprimento em metros
            $table->decimal('area', 10, 2)->nullable(); // Área calculada ou fixa (para MANDALA)
            $table->string('tenda_code')->nullable(); // Código da tenda (ex: tenda1, tenda2)
            $table->integer('tenda_number')->nullable(); // Número da tenda
            $table->string('base_id')->unique(); // ID base único (ex: tenda-QUADRADAS-10x10-tenda1)
            $table->boolean('can_calculate_price')->default(true); // false para MANDALA
            $table->decimal('price_per_square_meter', 10, 2)->default(22.00); // R$ por m²
            $table->decimal('fixed_price', 10, 2)->nullable(); // Preço fixo (para MANDALA)
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['category_id', 'active']);
            $table->index('base_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
