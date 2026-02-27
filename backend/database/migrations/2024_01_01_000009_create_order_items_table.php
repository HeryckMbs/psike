<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('product_name'); // Nome do produto (caso produto seja deletado)
            $table->decimal('width', 8, 2)->nullable(); // Dimensões customizadas
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('area', 10, 2)->nullable(); // Área calculada
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2); // Preço unitário
            $table->decimal('total_price', 10, 2); // Preço total (unit_price * quantity)
            $table->text('observations')->nullable(); // Observações específicas deste item
            $table->string('tenda_code')->nullable(); // Código da tenda
            $table->string('type')->nullable(); // Tipo da tenda (QUADRADAS, RETANGULAR, MANDALA)
            $table->integer('variation')->nullable(); // Variação escolhida
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
