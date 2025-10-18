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
            ["UserId"=>"1","IdRole"=>"1","Name"=>"Артем","Sename"=>"Казаков","Patronimic"=>"Максимович","BirthDate"=>"22/06/2004","Email"=>"temich@mail.ru","Password (cerified)"=>"password","Phone"=>"89776435855","Card"=>"1111 2222 3333"],
        ];
        foreach ($users as $user) {
            User::create($user);
        }
        $this->command->info("Создано друг");
    }
}
