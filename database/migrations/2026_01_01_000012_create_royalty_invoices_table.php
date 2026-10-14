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
        Schema::create('royalty_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('gross_sales', 14, 2);
            $table->decimal('royalty_percentage', 5, 2);
            $table->decimal('royalty_fee', 14, 2);
            $table->decimal('marketing_fee', 14, 2);
            $table->decimal('technology_fee', 14, 2)->default(0.00);
            $table->decimal('total_amount_due', 14, 2);
            $table->string('status', 30)->default('issued'); // draft, issued, paid, overdue, waived
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['outlet_id', 'status']);
            $table->index(['period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('royalty_invoices');
    }
};
