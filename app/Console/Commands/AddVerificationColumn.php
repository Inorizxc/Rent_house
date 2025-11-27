<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddVerificationColumn extends Command
{
    protected $signature = 'db:add-verification-column';
    protected $description = 'Add verification_denied_until column to users table';

    public function handle()
    {
        try {
            // Проверяем, существует ли колонка
            $columns = DB::select("PRAGMA table_info(users)");
            $columnExists = false;
            foreach ($columns as $column) {
                if ($column->name === 'verification_denied_until') {
                    $columnExists = true;
                    break;
                }
            }
            
            if ($columnExists) {
                $this->info('Column verification_denied_until already exists.');
                return 0;
            }
            
            // Добавляем колонку
            DB::statement('ALTER TABLE users ADD COLUMN verification_denied_until DATETIME NULL');
            $this->info('Column verification_denied_until added successfully!');
            return 0;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'duplicate column') !== false || 
                strpos($e->getMessage(), 'already exists') !== false) {
                $this->info('Column verification_denied_until already exists.');
                return 0;
            }
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}

