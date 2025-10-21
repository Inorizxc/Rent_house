<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $users = [
            ["user_id"=>"1","role_id"=>"1","name"=>"Артем","sename"=>"Казаков","patronymic"=>"Максимович","birth_date"=>"22.06.2004","email"=>"temich@mail.ru","password"=>"password","phone"=>"89776435855","card"=>"1111 2222 3333 4444",
            "need_verification" =>false],
            ["user_id"=>"2","role_id"=>"3","name"=>"Кирилл","sename"=>"Шпурт","patronymic"=>"Валерьевич","birth_date"=>"22.06.2004","email"=>"temich@mail.ru","password"=>"password","phone"=>"89776435855","card"=>"1111 2222 3333 4444",
            "need_verification" =>false],
        ];
        foreach ($users as $user) {
            User::create($user);
        }
        $this->command->info("Создано Пользователи");
    }
}
