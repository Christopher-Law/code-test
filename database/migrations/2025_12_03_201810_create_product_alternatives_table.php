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
        Schema::create('product_alternatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alternative_product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('relationship_type', ['same_brand', 'similar_item'])->default('similar_item');
            $table->decimal('similarity_score', 3, 2)->default(0.5)->comment('0-1 score indicating how similar');
            $table->timestamps();

            $table->unique(['product_id', 'alternative_product_id']);
            $table->index('relationship_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_alternatives');
    }
};
