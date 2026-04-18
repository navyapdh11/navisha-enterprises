<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->integer('stock_qty')->default(0);
            $table->integer('reserved_stock')->default(0);
            $table->integer('reorder_level')->default(10);
            $table->string('warehouse_location', 100)->nullable();
            $table->timestamp('last_restocked_at')->nullable();
            $table->timestamps();

            $table->index('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_levels');
    }
};
