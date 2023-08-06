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
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('pay', ['nonpayed', 'payed'])->default('nonpayed');
            $table->enum('state', ['created', 'selled', 'arrived', 'received'])->default('created');
        });

        Schema::table('verifications_tokens', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->constrained('orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
