<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('melhor_envio_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('access_token');
            $table->string('refresh_token')->nullable();
            $table->integer('expires_in');
            $table->timestamp('expires_at');
            $table->string('token_type')->default('Bearer');
            $table->string('scope')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('active');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('melhor_envio_tokens');
    }
};
