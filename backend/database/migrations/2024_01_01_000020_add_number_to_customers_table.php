<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('number')->nullable()->after('id');
        });
        
        // Preencher números para registros existentes
        $customers = \App\Models\Customer::all();
        $counter = 1;
        foreach ($customers as $customer) {
            if (empty($customer->number)) {
                $customer->number = 'CLI-' . str_pad($counter, 6, '0', STR_PAD_LEFT);
                $customer->save();
                $counter++;
            }
        }
        
        // Agora tornar o campo obrigatório e único
        Schema::table('customers', function (Blueprint $table) {
            $table->string('number')->nullable(false)->unique()->change();
            $table->index('number');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['number']);
            $table->dropColumn('number');
        });
    }
};
