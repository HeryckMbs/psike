<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cost_types', function (Blueprint $table) {
            $table->enum('type', ['receita', 'despesa'])->default('despesa')->after('slug');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('cost_types', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }
};
