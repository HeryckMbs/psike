<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_item_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('original_width', 8, 2); // Dimensão original
            $table->decimal('original_height', 8, 2);
            $table->decimal('custom_width', 8, 2)->nullable(); // Dimensão customizada
            $table->decimal('custom_height', 8, 2)->nullable();
            $table->text('notes')->nullable(); // Observações sobre a mudança
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_dimensions');
    }
};
