<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Services\OrderService\OrderService;
use App\Services\YandexGeocoder;
use Carbon\Carbon;
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
        $service = app(OrderService::class);
        $dates = [
       '2024-01-01',
       '2024-01-05',
       '2024-01-10',
       '2024-01-15',
       '2024-01-20',
       '2024-02-01',
        ];
        print_r($service->removeDatesBetween($dates,Carbon::parse('2024-01-05'),10)); 

    }
}
