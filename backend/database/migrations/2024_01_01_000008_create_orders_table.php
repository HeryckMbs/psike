<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // Número do pedido
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->foreignId('status_id')->constrained('order_statuses')->onDelete('restrict');
            $table->decimal('total_amount', 10, 2); // Valor total do orçamento
            $table->boolean('custom_price')->default(false); // Se true, não usar valor padrão
            $table->decimal('custom_price_value', 10, 2)->nullable(); // Valor manual se custom_price = true
            $table->text('notes')->nullable(); // Observações gerais do pedido
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status_id', 'created_at']);
            $table->index('order_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
