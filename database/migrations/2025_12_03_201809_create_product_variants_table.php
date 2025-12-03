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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('supplier_sku')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->enum('availability_status', ['in_stock', 'backordered', 'out_of_stock'])->default('in_stock');
            $table->integer('estimated_delivery_days')->nullable();
            $table->date('estimated_delivery_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_id', 'supplier_id']);
            $table->index('price');
            $table->index('availability_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
