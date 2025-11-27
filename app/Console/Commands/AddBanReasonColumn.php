<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddBanReasonColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:add-ban-reason-column';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавляет колонку ban_reason в таблицу users (для SQLite)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Проверяем существование колонки через PRAGMA для SQLite
        $columns = DB::select("PRAGMA table_info(users)");
        $columnNames = array_column($columns, 'name');
        
        if (in_array('ban_reason', $columnNames)) {
            $this->info('Колонка ban_reason уже существует в таблице users.');
            return 0;
        }

        try {
            DB::statement('ALTER TABLE users ADD COLUMN ban_reason TEXT NULL');
            $this->info('Колонка ban_reason успешно добавлена в таблицу users.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Ошибка при добавлении колонки: ' . $e->getMessage());
            $this->error('Попробуйте выполнить SQL запрос вручную: ALTER TABLE users ADD COLUMN ban_reason TEXT NULL');
            return 1;
        }
    }
}

