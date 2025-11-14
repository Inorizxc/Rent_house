<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;

use App\Services\YandexGeocoder;
class ShowRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:show-roles';

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
        $count = Role::count();
        $roles = Role::all();
        foreach ($roles as $role) {
            $this->info("{$role->uniq_name} + {$role->role_id}+ {$count}");
        }
        
        $geocoder = new YandexGeocoder();
        print_r($geocoder->getCoordinates("Навашина+9")); 

    }
}
