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
        Schema::table('verifications_tokens', function (Blueprint $table) {
            $table->enum('task', ['verify_email', 'password_reset', 'other']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //verifications_tokens
    }
};
