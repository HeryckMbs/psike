<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('path'); // Caminho da imagem
            $table->string('filename'); // Nome do arquivo
            $table->boolean('is_main')->default(false); // Imagem principal
            $table->integer('variation')->nullable(); // Número da variação (1, 2, 3, etc)
            $table->integer('order')->default(0); // Ordem de exibição
            $table->timestamps();

            $table->index(['product_id', 'is_main']);
            $table->index(['product_id', 'variation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
