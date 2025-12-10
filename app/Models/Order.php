<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\enum\OrderStatus;

class Order extends Model
{
    protected $table = "orders";
    protected $primaryKey = "order_id";
    public $incrementing = true;
    
    protected $fillable =
    [
        "order_id",
        "house_id",
        "date_of_order",
        "day_count",
        "total_amount",
        "customer_id",
        "order_status",
        "original_data",
        "refunded_at",
        ];
    protected $casts = [
        "order_status" => OrderStatus::class,
        "total_amount" => "decimal:2",
        "refunded_at" => "datetime",
    ];
    
    protected function castAttribute($key, $value)
    {
        if ($key === 'order_status' && !($value instanceof OrderStatus)) {
            $mapping = [
                'Ожидается' => OrderStatus::PENDING->value,
                'Рассмотрение' => OrderStatus::PENDING->value,
                'Обработка' => OrderStatus::PROCESSING->value,
                'Завершено' => OrderStatus::COMPLETED->value,
                'Отменено' => OrderStatus::CANCELLED->value,
                'Возврат' => OrderStatus::REFUND->value,
            ];
            
            if (isset($mapping[$value])) {
                $value = $mapping[$value];
            }
 
            try {
                return OrderStatus::from($value);
            } catch (\ValueError $e) {
                return OrderStatus::PENDING;
            }
        }
        
        return parent::castAttribute($key, $value);
    }
    
    public function house(){
        return $this->belongsTo(House::class,"house_id","house_id");
    }

    public function customer(){
        return $this->belongsTo(User::class,"customer_id","user_id");
    }

    public function user(){
        return $this->customer();
    }

    //public function order_status(){
    //    return $this->hasOne(OrderStatus::class,"order_status_id","order_status_id");
    //}

    public function isRefunded(): bool
    {
        return $this->refunded_at !== null;
    }
}
