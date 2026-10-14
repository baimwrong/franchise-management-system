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
        Schema::create('outlet_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('auditor_id')->constrained('users')->cascadeOnDelete();
            $table->date('audit_date');
            $table->decimal('food_safety_score', 5, 2); // 0.00 to 100.00
            $table->decimal('cleanliness_score', 5, 2);
            $table->decimal('brand_standards_score', 5, 2);
            $table->decimal('customer_service_score', 5, 2);
            $table->decimal('overall_score', 5, 2);
            $table->boolean('passed')->default(true);
            $table->text('findings')->nullable();
            $table->text('corrective_actions')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();

            $table->index(['outlet_id', 'audit_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlet_audits');
    }
};
