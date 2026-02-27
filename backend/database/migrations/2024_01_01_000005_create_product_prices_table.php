<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('option_key')->nullable(); // Chave da opção (ex: "color:red")
            $table->decimal('price', 10, 2); // Preço customizado
            $table->text('notes')->nullable(); // Observações sobre o preço
            $table->timestamps();

            $table->index('product_id');
            $table->unique(['product_id', 'option_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
