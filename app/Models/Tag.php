<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Model;
class Tag extends Model
{
    protected $table = "tags";
    protected $primaryKey = "tag_id";
    public $incrementing = true;
    
    protected $fillable = [
        'tag_id',
        'name',
        'description',
    ];

    public function house_tag(){
        return $this->hasMany(HouseTag::class,"tag_id","tag_id");
    }

}
