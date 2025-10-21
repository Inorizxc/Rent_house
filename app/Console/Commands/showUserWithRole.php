<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\User;
class showUserWithRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    
    protected $signature = 'app:show-user-with-role {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        //$table = DB::table("SELECT u.name as name, u.sename r.name from users as u
        //right join on roles as r
        //where $id = r.IdRole and r.IdRole = u.IdRole");
        $table1 = Role::with('user')->where("role_id",$id)->get();
        $user= User::with("roles")->where("role_id",$id)->get();
        foreach ($table1 as $role) {
            foreach($user as $user1){
                $this->info("{$user1->name}: {$role->name}");
            }
            
        }
    }
}
