<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            $table->string('melhor_envio_quote_id')->nullable()->after('order_id');
            $table->integer('melhor_envio_service_id')->nullable()->after('melhor_envio_quote_id');
            $table->string('melhor_envio_label_id')->nullable()->after('melhor_envio_service_id');
            $table->string('from_postal_code', 10)->after('melhor_envio_label_id');
            $table->string('to_postal_code', 10)->after('from_postal_code');
            $table->decimal('total_weight', 10, 2)->nullable()->after('to_postal_code');
            $table->decimal('total_volume', 10, 2)->nullable()->after('total_weight');
            $table->json('package_dimensions')->nullable()->after('total_volume');
            $table->string('selected_service_name')->nullable()->after('package_dimensions');
            $table->integer('delivery_time')->nullable()->after('estimated_days');
        });
    }

    public function down(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            $table->dropColumn([
                'melhor_envio_quote_id',
                'melhor_envio_service_id',
                'melhor_envio_label_id',
                'from_postal_code',
                'to_postal_code',
                'total_weight',
                'total_volume',
                'package_dimensions',
                'selected_service_name',
                'delivery_time',
            ]);
        });
    }
};
