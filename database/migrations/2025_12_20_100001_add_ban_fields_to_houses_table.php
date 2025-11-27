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
        Schema::table('houses', function (Blueprint $table) {
            if (!Schema::hasColumn('houses', 'banned_until')) {
                $table->dateTime('banned_until')->nullable();
            }
            if (!Schema::hasColumn('houses', 'is_banned_permanently')) {
                $table->boolean('is_banned_permanently')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->dropColumn(['banned_until', 'is_banned_permanently']);
        });
    }
};

