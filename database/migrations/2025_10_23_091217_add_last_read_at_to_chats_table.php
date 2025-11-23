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
        Schema::table('chats', function (Blueprint $table) {
            $table->timestamp('user_last_read_at')->nullable()->after('updated_at');
            $table->timestamp('rent_dealer_last_read_at')->nullable()->after('user_last_read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn(['user_last_read_at', 'rent_dealer_last_read_at']);
        });
    }
};

