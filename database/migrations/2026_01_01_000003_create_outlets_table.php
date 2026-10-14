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
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('outlet_code', 50)->unique();
            $table->string('franchisee_name')->default('Corporate / Headquarters');
            $table->string('type', 30)->default('franchise'); // franchise, company_owned, kiosk, express
            $table->string('status', 30)->default('active'); // active, pending_approval, renovation, suspended, closed
            $table->string('phone', 50);
            $table->string('email');
            $table->text('address');
            $table->string('city', 100);
            $table->string('state_province', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('tax_identification_number', 50)->nullable();
            $table->decimal('tax_rate_percentage', 5, 2)->default(10.00);
            $table->date('opening_date')->nullable();
            $table->json('operating_hours')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->index(['brand_id', 'status']);
            $table->index('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};
