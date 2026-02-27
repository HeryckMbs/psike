<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome do status (ex: "Novo", "Em Produção", "Enviado")
            $table->string('slug')->unique();
            $table->string('color')->default('#1976d2'); // Cor para o Kanban
            $table->integer('order')->default(0); // Ordem de exibição no Kanban
            $table->boolean('is_default')->default(false); // Status padrão para novos pedidos
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_statuses');
    }
};
