<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ["service_id"=>"1","name"=>"Двухэтажный","description"=>"Ну двухэтажный и че"],
        ];
        foreach ($services as $service) {
            Service::create($service);
        }
        $this->command->info("Создано Сервис");
        
        
    }
}
