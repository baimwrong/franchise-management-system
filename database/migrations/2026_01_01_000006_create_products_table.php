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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('barcode', 100)->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit_of_measure', 30)->default('unit'); // unit, cup, kg, liter, box, pack
            $table->decimal('cost_price', 12, 2)->default(0.00); // wholesale price from HQ
            $table->decimal('suggested_retail_price', 12, 2)->default(0.00); // MSRP
            $table->decimal('minimum_selling_price', 12, 2)->default(0.00);
            $table->boolean('is_track_inventory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->index(['brand_id', 'category_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
