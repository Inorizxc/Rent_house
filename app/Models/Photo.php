<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Http\UploadedFile;
use App\Models\House;
class Photo extends Model
{
    protected $table = "photos";
    protected $primaryKey = "photo_id";
    public $incrementing = true;
    
    protected $fillable = [
        'photo_id',
        'house_id',
        "user_id",
        'path',
        'name',
    ];

    public function house(){
        return $this->belongsTo(House::class,"house_id","house_id");
    }
    public function user(){
        return $this->belongsTo(User::class,"user_id","user_id");
    }

    public static function saveUploadedFile(UploadedFile $file, House $house): Photo
    {
        // Генерируем уникальное имя файла
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Путь для сохранения: houses/{house_id}/{file_name}
        $path = $file->storeAs("image/{$house->house_id}", $fileName, 'public');

        // Создаем запись в базе
        return self::create([
            'house_id' => $house->house_id,
            'user_id' => $house->user_id, 
            'path' => $path,
            'name' => $file->getClientOriginalName(),
        ]);
    }

    

}
