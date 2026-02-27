<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_costs', function (Blueprint $table) {
            if (!Schema::hasColumn('order_costs', 'cost_type_id')) {
                $table->foreignId('cost_type_id')->nullable()->after('order_id')->constrained()->onDelete('restrict');
                $table->index('cost_type_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_costs', function (Blueprint $table) {
            if (Schema::hasColumn('order_costs', 'cost_type_id')) {
                $table->dropForeign(['cost_type_id']);
                $table->dropIndex(['cost_type_id']);
                $table->dropColumn('cost_type_id');
            }
        });
    }
};
