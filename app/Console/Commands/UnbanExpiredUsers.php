<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\House;
use App\Models\Role;
use Carbon\Carbon;

class UnbanExpiredUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bans:unban-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Автоматически снимает баны с пользователей и домов, у которых истек срок временного бана';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bannedRole = Role::where('uniq_name', 'Banned')->first();
        
        if (!$bannedRole) {
            $this->error('Роль "Забанен" не найдена в базе данных.');
            return 1;
        }

        // Устанавливаем московское время
        $now = Carbon::now('Europe/Moscow');
        $unbannedUsersCount = 0;
        $unbannedHousesCount = 0;

        // Отладочная информация
        $this->line("Текущее время: {$now->format('Y-m-d H:i:s')}");
        
        // Получаем всех забаненных пользователей для отладки
        $allBannedUsers = User::where('role_id', $bannedRole->role_id)
            ->whereNotNull('banned_until')
            ->get();
        
        $this->line("Всего забаненных пользователей с датой окончания: {$allBannedUsers->count()}");
        
        // ЗАКОММЕНТИРОВАНО: Автоматическое разбанивание пользователей с истекшим временным баном
        // Разбаниваем пользователей с истекшим временным баном
        // Используем фильтрацию в PHP, так как SQLite может некорректно сравнивать даты
        // $expiredBannedUsers = $allBannedUsers->filter(function($user) use ($now) {
        //     if (!$user->banned_until) {
        //         return false;
        //     }
        //     
        //     // Получаем дату бана, учитывая что она может быть строкой или Carbon объектом
        //     try {
        //         // Если это уже Carbon объект (из casts), используем его и устанавливаем московское время
        //         if ($user->banned_until instanceof \Carbon\Carbon) {
        //             $banDate = $user->banned_until->setTimezone('Europe/Moscow');
        //         } else {
        //             // Иначе парсим строку с указанием московского времени
        //             $banDate = \Carbon\Carbon::parse($user->banned_until, 'Europe/Moscow');
        //         }
        //         
        //         // Отладочная информация
        //         $isExpired = $banDate->isPast() || $banDate->lte($now);
        //         $this->line("  Пользователь #{$user->user_id}: бан до {$banDate->format('Y-m-d H:i:s')}, текущее время: {$now->format('Y-m-d H:i:s')}, истек: " . ($isExpired ? 'ДА' : 'НЕТ'));
        //         
        //         return $isExpired;
        //     } catch (\Exception $e) {
        //         $this->error("Ошибка при обработке пользователя #{$user->user_id}: " . $e->getMessage() . " (значение: " . var_export($user->banned_until, true) . ")");
        //         return false;
        //     }
        // });

        // foreach ($expiredBannedUsers as $user) {
        //     $user->unban();
        //     $unbannedUsersCount++;
        //     $this->info("Пользователь #{$user->user_id} разбанен (истек срок бана).");
        // }

        // ЗАКОММЕНТИРОВАНО: Автоматическое разбанивание домов с истекшим временным баном
        // Разбаниваем дома с истекшим временным баном
        // $allBannedHouses = House::whereNotNull('banned_until')->get();
        // $this->line("Всего забаненных домов с датой окончания: {$allBannedHouses->count()}");
        // 
        // $expiredBannedHouses = $allBannedHouses->filter(function($house) use ($now) {
        //     if (!$house->banned_until) {
        //         return false;
        //     }
        //     $banDate = $house->banned_until instanceof \Carbon\Carbon 
        //         ? $house->banned_until->setTimezone('Europe/Moscow')
        //         : \Carbon\Carbon::parse($house->banned_until, 'Europe/Moscow');
        //     return $banDate->isPast() || $banDate->lte($now);
        // });

        // foreach ($expiredBannedHouses as $house) {
        //     if ($house->isBanned()) {
        //         $house->is_banned_permanently = false;
        //         $house->banned_until = null;
        //         $house->save();
        //         $unbannedHousesCount++;
        //         $this->info("Дом #{$house->house_id} разбанен (истек срок бана).");
        //     }
        // }

        if ($unbannedUsersCount > 0 || $unbannedHousesCount > 0) {
            $this->info("Всего разбанено: {$unbannedUsersCount} пользователей, {$unbannedHousesCount} домов.");
        } else {
            $this->line('Нет истекших банов для снятия.');
        }

        $this->line("Проверка завершена. Текущее время: {$now->format('Y-m-d H:i:s')}");

        return 0;
    }
}

