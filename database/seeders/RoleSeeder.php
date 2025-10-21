<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $table = DB::table("SELECT count() FROM roles");
        $count1 = Role::count();
        if($count1<4){
            $this->command->info("Создание ролей");
        $this->command->info("____________");
            $roles = [
                ["uniq_name"=> "Admin","name"=> "Администратор","description"=> "Администрирует"],
                ["uniq_name"=> "RentDealer","name"=> "Арендодатель","description"=> "Сдает дома"],
                ["uniq_name"=> "User","name"=> "Пользователь","description"=> "Пользуется"],
                ["uniq_name"=> "Guest","name"=> "Гость","description"=> "Гостит"],
            ];
            foreach ($roles as $role) {
                $role1 = Role::firstOrCreate($role);
                $this->command->info("RolesCreated: {$role1->uniq_name} , {$role1->role_id}");
            }
            $count = Role::count();
            $this->command->info("{$count}");
            DB::statement("DROP TRIGGER IF EXISTS block_roles_insert");
            DB::statement("
                CREATE TRIGGER block_roles_insert
                BEFORE INSERT ON roles
                BEGIN
                    SELECT RAISE(ABORT, 'Пососи кирюха со своим созданием');
                END;
            ");
            DB::statement("DROP TRIGGER IF EXISTS block_roles_update");
            DB::statement("
                CREATE TRIGGER block_roles_update
                BEFORE INSERT ON roles
                BEGIN
                    SELECT RAISE(ABORT, 'Пососи кирюха со своим созданием');
                END;
            ");
            }
        
    }
}
