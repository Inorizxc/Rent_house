<?php

namespace App\Models;

use App\roletrait;
use Illuminate\Database\Eloquent\Model;
use App\Observers\RoleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([RoleObserver::class])]
class Role extends Model
{
    protected $table = "roles";
    protected $primaryKey = "IdRole";
    public $incrementing = true;
   
    protected $fillable =
    [   "UniqName",
        "Name",
        "Description"
        ];
    
    protected static function boot(){
        
        parent::boot();
       
        
    }
}
