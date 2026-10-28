<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Soporta Product::active()->lowStock()->orderBy('stock')
            $table->index(['is_active', 'stock', 'minimum_stock'], 'products_active_stock_idx');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            // Soporta agregaciones filtradas por rango de fechas y tipo
            $table->index(['created_at', 'type'], 'movements_created_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_stock_idx');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex('movements_created_type_idx');
        });
    }
};
