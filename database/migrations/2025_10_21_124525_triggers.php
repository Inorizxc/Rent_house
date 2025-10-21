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
        // Триггеры для ордеров
        
        DB::statement("CREATE TRIGGER block_order_update
            BEFORE UPDATE ON orders
            BEGIN
                SELECT RAISE(ABORT, 'Нельзя изменять чеки после их создания.');
            END;
        ");
        DB::statement("CREATE TRIGGER block_order_delete
            BEFORE DELETE ON orders
            BEGIN
                SELECT RAISE(ABORT, 'Нельзя удалять чеки после их создания.');
            END;
        ");

        // триггеры для домов
        DB::statement("CREATE TRIGGER block_house_creation_from_role
            BEFORE INSERT ON houses
            when (select role_id from users
                where users.user_id = new.user_id)>=2

            BEGIN
            select RAISE(ABORT,'Только арендодатели могут создавать дома');
            END;
        ");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS block_order_update");
        DB::statement("DROP TRIGGER IF EXISTS block_order_delete");
        DB::statement("DROP TRIGGER IF EXISTS block_house_creation_from_role");
    }
};
