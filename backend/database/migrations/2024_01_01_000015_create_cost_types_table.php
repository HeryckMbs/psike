<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->enum('type', ['receita', 'despesa'])->default('despesa');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('slug');
            $table->index('active');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_types');
    }
};
