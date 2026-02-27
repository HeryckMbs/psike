<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_costs', function (Blueprint $table) {
            if (Schema::hasColumn('order_costs', 'name')) {
                $table->dropColumn('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_costs', function (Blueprint $table) {
            $table->string('name')->after('cost_type_id');
        });
    }
};
