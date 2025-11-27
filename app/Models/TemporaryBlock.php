<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TemporaryBlock extends Model
{
    protected $table = "temporary_blocks";
    protected $primaryKey = "temporary_block_id";
    public $incrementing = true;

    protected $fillable = [
        'house_id',
        'user_id',
        'dates',
        'expires_at',
    ];

    protected $casts = [
        'dates' => 'array',
        'expires_at' => 'datetime',
    ];

    public function house()
    {
        return $this->belongsTo(House::class, 'house_id', 'house_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Метод для очистки истекших временных блокировок
    public static function cleanExpired()
    {
        self::where('expires_at', '<', now())->delete();
    }
}
