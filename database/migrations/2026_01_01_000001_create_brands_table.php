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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('tagline')->nullable();
            $table->string('corporate_tax_id', 50)->nullable();
            $table->text('hq_address')->nullable();
            $table->string('contact_email');
            $table->string('contact_phone', 50);
            $table->decimal('royalty_rate_percentage', 5, 2)->default(5.00);
            $table->decimal('marketing_fund_percentage', 5, 2)->default(2.00);
            $table->string('currency', 10)->default('USD');
            $table->string('currency_symbol', 10)->default('$');
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
