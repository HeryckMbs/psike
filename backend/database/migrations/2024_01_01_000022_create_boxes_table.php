<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boxes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome da caixa (ex: "Caixa Pequena", "Caixa Média")
            $table->decimal('width', 8, 2); // Largura em centímetros
            $table->decimal('height', 8, 2); // Altura em centímetros
            $table->decimal('length', 8, 2); // Comprimento em centímetros
            $table->decimal('weight', 8, 2); // Peso fixo em kg
            $table->boolean('active')->default(true); // Se a caixa está ativa
            $table->timestamps();

            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boxes');
    }
};
