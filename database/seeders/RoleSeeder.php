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
        $this->command->info("Создание ролей");
        $this->command->info("____________");
        $roles = [
            ["UniqName"=> "Admin","Name"=> "Администратор","Description"=> "Администрирует"],
            ["UniqName"=> "RentDealer","Name"=> "Арендодатель","Description"=> "Сдает дома"],
            ["UniqName"=> "User","Name"=> "Пользователь","Description"=> "Пользуется"],
            ["UniqName"=> "Guest","Name"=> "Гость","Description"=> "Гостит"],
        ];
        If(Role::count()==4){
            $this->command->info("Роли уже созданы");
        }
        else{
            foreach ($roles as $role) {
            $role1 = Role::firstOrCreate($role);
            $this->command->info("RolesCreated: {$role1->UniqName} , {$role1->IdRole}");
            }
            $this->command->info("Дальнейшее создание ролей заблокировано!");
        }
        
        
    }
}
