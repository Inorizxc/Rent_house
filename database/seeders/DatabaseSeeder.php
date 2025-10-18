<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            OrderStatusSeeder::class,
            OrderSeeder::class,
            OrderCalendarSeeder::class,
            PriceListSeeder::class,
            RentTypeSeeder::class,
            HouseTypeSeeder::class,
            TagSeeder::class,
            HouseTagSeeder::class,
            ServiceSeeder::class,
            HouseServiceSeeder::class,
            HouseSeeder::class,
        ]);
    }
}
