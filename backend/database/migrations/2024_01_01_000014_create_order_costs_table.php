<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('cost_type_id')->constrained()->onDelete('restrict');
            $table->decimal('value', 10, 2);
            $table->timestamps();

            $table->index('order_id');
            $table->index('cost_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_costs');
    }
};
