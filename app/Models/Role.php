<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = "roles";
    protected $primaryKey = "IdRole";
    public $incrementing = true;
    
    protected $fillable =
    [
        "UniqName",
        "Name",
        "Description"
        ];
}
