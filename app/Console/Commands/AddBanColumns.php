<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddBanColumns extends Command
{
    protected $signature = 'db:add-ban-columns';
    protected $description = 'Add ban-related columns to users and houses tables';

    public function handle()
    {
        try {
            // Проверяем и добавляем колонки в таблицу users
            $usersColumns = DB::select("PRAGMA table_info(users)");
            $usersColumnNames = array_column($usersColumns, 'name');
            
            if (!in_array('banned_until', $usersColumnNames)) {
                DB::statement('ALTER TABLE users ADD COLUMN banned_until DATETIME NULL');
                $this->info('Column banned_until added to users table.');
            } else {
                $this->info('Column banned_until already exists in users table.');
            }
            
            if (!in_array('is_banned_permanently', $usersColumnNames)) {
                DB::statement('ALTER TABLE users ADD COLUMN is_banned_permanently BOOLEAN DEFAULT 0');
                $this->info('Column is_banned_permanently added to users table.');
            } else {
                $this->info('Column is_banned_permanently already exists in users table.');
            }
            
            // Проверяем и добавляем колонки в таблицу houses
            $housesColumns = DB::select("PRAGMA table_info(houses)");
            $housesColumnNames = array_column($housesColumns, 'name');
            
            if (!in_array('banned_until', $housesColumnNames)) {
                DB::statement('ALTER TABLE houses ADD COLUMN banned_until DATETIME NULL');
                $this->info('Column banned_until added to houses table.');
            } else {
                $this->info('Column banned_until already exists in houses table.');
            }
            
            if (!in_array('is_banned_permanently', $housesColumnNames)) {
                DB::statement('ALTER TABLE houses ADD COLUMN is_banned_permanently BOOLEAN DEFAULT 0');
                $this->info('Column is_banned_permanently added to houses table.');
            } else {
                $this->info('Column is_banned_permanently already exists in houses table.');
            }
            
            $this->info('All ban columns have been added successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}

