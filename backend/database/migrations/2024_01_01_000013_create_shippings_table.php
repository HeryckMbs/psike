<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('carrier')->nullable(); // Nome da transportadora
            $table->string('tracking_code')->nullable(); // Código de rastreamento
            $table->string('status')->default('pending'); // pending, shipped, delivered, returned
            $table->decimal('cost', 10, 2)->nullable(); // Custo do frete
            $table->integer('estimated_days')->nullable(); // Prazo estimado em dias
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('tracking_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shippings');
    }
};
