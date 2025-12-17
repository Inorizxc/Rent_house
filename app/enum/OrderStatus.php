<?php
namespace App\enum;

enum OrderStatus:String { 
    case PENDING ='Рассмотрение';
    case PROCESSING = 'Обработка'; 
    case COMPLETED = "Завершено"; 
    case CANCELLED = 'Отменено';
    case REFUND = 'Возврат'; 
    case PREREFUND = 'Ожидает возврата'; 
    case PREPAYMENT = 'Предоплачено'; }