<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $roles = [
            ["UniqName"=> "Admin","Name"=> "Администратор","Description"=> "Администрирует"],
            ["UniqName"=> "RentDealer","Name"=> "Арендодатель","Description"=> "Сдает дома"],
            ["UniqName"=> "User","Name"=> "Пользователь","Description"=> "Пользуется"],
            ["UniqName"=> "Guest","Name"=> "Гость","Description"=> "Гостит"],
        ];
        foreach ($roles as $role) {
            Role::create($role);
        }
        $this->command->info("Создано брат");
    }
}
