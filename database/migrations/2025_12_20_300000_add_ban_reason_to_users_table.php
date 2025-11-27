<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Для SQLite используем PRAGMA для проверки и прямой SQL запрос для добавления
        $columns = DB::select("PRAGMA table_info(users)");
        $columnNames = array_column($columns, 'name');
        
        if (!in_array('ban_reason', $columnNames)) {
            DB::statement('ALTER TABLE users ADD COLUMN ban_reason TEXT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'ban_reason')) {
                $table->dropColumn('ban_reason');
            }
        });
    }
};

