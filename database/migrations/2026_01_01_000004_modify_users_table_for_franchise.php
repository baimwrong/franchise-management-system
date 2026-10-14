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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('staff')->after('password'); // super_admin, franchise_owner, outlet_manager, staff
            $table->foreignId('outlet_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->string('phone', 50)->nullable()->after('outlet_id');
            $table->boolean('is_active')->default(true)->after('phone');
            $table->timestamp('last_login_at')->nullable()->after('is_active');

            $table->index(['role', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['outlet_id']);
            $table->dropColumn(['role', 'outlet_id', 'phone', 'is_active', 'last_login_at']);
        });
    }
};
