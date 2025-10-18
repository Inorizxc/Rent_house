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
            ["Name"=> "Артем","Email"=> "Temich@mail;.com","password"=> "1description"],
            ["Name"=> "Кирилл","Email"=> "Korotkiy@mail;.com","password"=> "2description"],
            ["Name"=> "Кирилл","Email"=> "Dlinniy@mail;.com","password"=> "3description"],
        ];
        foreach ($users as $user) {
            User::create($user);
        }
        $this->command->info("Создано друг");
    }
}
