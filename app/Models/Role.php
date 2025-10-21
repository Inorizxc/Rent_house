<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = "roles";
    protected $primaryKey = "id_role";
    public $incrementing = true;
   
    protected $fillable =
    [   "uniq_name",
        "name",
        "description"
        ];
    
    protected static function boot(){
        parent::boot();
    }

    public function user(){
        return $this->hasMany(User::class,"IdRole","IdRole");
    }
}
