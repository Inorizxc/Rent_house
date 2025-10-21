<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ["tag_id"=>"1","name"=>"У метро","description"=>"Ну у метро и че"],
        ];
        foreach ($tags as $tag) {
            Tag::create($tag);
        }
        $this->command->info("Создано Тэг");
        
        
    }
}
