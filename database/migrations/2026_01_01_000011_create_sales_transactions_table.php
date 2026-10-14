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
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // cashier/server
            $table->string('transaction_code', 50)->unique();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->decimal('subtotal', 14, 2);
            $table->decimal('discount_amount', 14, 2)->default(0.00);
            $table->decimal('tax_amount', 14, 2)->default(0.00);
            $table->decimal('total_amount', 14, 2);
            $table->string('payment_method', 30)->default('cash'); // cash, credit_card, debit_card, qris, bank_transfer
            $table->string('payment_status', 30)->default('paid'); // paid, refunded, void
            $table->timestamp('transaction_date')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['outlet_id', 'transaction_date']);
            $table->index('payment_status');
        });

        Schema::create('sales_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->string('sku', 50);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('quantity', 12, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_transaction_items');
        Schema::dropIfExists('sales_transactions');
    }
};
