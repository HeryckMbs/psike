<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cost_types', function (Blueprint $table) {
            $table->dropIndex(['number']);
            $table->dropColumn('number');
        });
    }

    public function down(): void
    {
        Schema::table('cost_types', function (Blueprint $table) {
            $table->string('number')->nullable()->after('slug');
        });
        
        $costTypes = \App\Models\CostType::all();
        $counter = 1;
        foreach ($costTypes as $costType) {
            if (empty($costType->number)) {
                $costType->number = str_pad($counter, 3, '0', STR_PAD_LEFT);
                $costType->save();
                $counter++;
            }
        }
        
        Schema::table('cost_types', function (Blueprint $table) {
            $table->string('number')->nullable(false)->unique()->change();
            $table->index('number');
        });
    }
};
