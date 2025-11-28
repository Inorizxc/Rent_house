<?php

namespace App\Services\OrderService;

use App\Models\House;
use App\Models\TemporaryBlock;
use App\Models\User;
use Carbon\Carbon;

class OrderValidationService
{
    /**
     * Генерирует массив дат для блокировки
     */
    public function generateDatesToBlock(string $checkinDate, string $checkoutDate): array
    {
        $checkin = new \DateTime($checkinDate);
        $checkout = new \DateTime($checkoutDate);
        $checkoutForCheck = clone $checkout;
        $checkoutForCheck->modify('-1 day');

        $datesToBlock = [];
        $current = clone $checkin;
        
        while ($current <= $checkoutForCheck) {
            $datesToBlock[] = $current->format('Y-m-d');
            $current->modify('+1 day');
        }

        return $datesToBlock;
    }

    /**
     * Проверяет доступность дат для бронирования
     */
    public function checkDatesAvailability(House $house, array $datesToBlock, ?User $user = null): array
    {
        // Очищаем истекшие блокировки
        TemporaryBlock::cleanExpired();

        // Получаем забронированные даты из календаря
        $calendar = $house->house_calendar;
        $bookedDates = $calendar ? ($calendar->dates ?? []) : [];
        
        // Получаем активные временные блокировки для этого дома
        $temporaryBlocks = TemporaryBlock::where('house_id', $house->house_id)
            ->where('expires_at', '>', now())
            ->get();
        
        $temporaryBlockedDates = [];
        foreach ($temporaryBlocks as $block) {
            $temporaryBlockedDates = array_merge($temporaryBlockedDates, $block->dates ?? []);
        }
        $temporaryBlockedDates = array_unique($temporaryBlockedDates);

        // Проверяем каждую дату
        foreach ($datesToBlock as $date) {
            // Проверяем постоянные бронирования
            if (in_array($date, $bookedDates)) {
                return [
                    'available' => false,
                    'error' => "Дата {$date} уже занята",
                    'date' => $date,
                ];
            }

            // Проверяем временные блокировки других пользователей
            if (in_array($date, $temporaryBlockedDates) && $user) {
                $blockingUser = TemporaryBlock::where('house_id', $house->house_id)
                    ->where('expires_at', '>', now())
                    ->whereJsonContains('dates', $date)
                    ->where('user_id', '!=', $user->user_id)
                    ->first();
                
                if ($blockingUser) {
                    return [
                        'available' => false,
                        'error' => "Дата {$date} временно заблокирована другим пользователем",
                        'date' => $date,
                    ];
                }
            }
        }

        return ['available' => true];
    }

    /**
     * Создает временную блокировку
     */
    public function createTemporaryBlock(House $house, User $user, array $dates, int $minutes = 10): TemporaryBlock
    {
        // Удаляем старые временные блокировки этого пользователя для этого дома
        TemporaryBlock::where('house_id', $house->house_id)
            ->where('user_id', $user->user_id)
            ->delete();

        // Создаем новую временную блокировку
        return TemporaryBlock::create([
            'house_id' => $house->house_id,
            'user_id' => $user->user_id,
            'dates' => $dates,
            'expires_at' => now()->addMinutes($minutes),
        ]);
    }

    /**
     * Проверяет и валидирует временную блокировку
     */
    public function validateTemporaryBlock(int $temporaryBlockId, int $houseId, int $userId, array $expectedDates): ?TemporaryBlock
    {
        $temporaryBlock = TemporaryBlock::where('temporary_block_id', $temporaryBlockId)
            ->where('user_id', $userId)
            ->where('house_id', $houseId)
            ->where('expires_at', '>', now())
            ->first();

        if (!$temporaryBlock) {
            return null;
        }

        // Проверяем, что даты совпадают
        $blockDates = $temporaryBlock->dates ?? [];
        sort($blockDates);
        sort($expectedDates);
        
        if ($blockDates !== $expectedDates) {
            $temporaryBlock->delete();
            return null;
        }

        return $temporaryBlock;
    }

    /**
     * Удаляет временную блокировку
     */
    public function removeTemporaryBlock(int $temporaryBlockId, int $houseId, int $userId): bool
    {
        $temporaryBlock = TemporaryBlock::where('temporary_block_id', $temporaryBlockId)
            ->where('user_id', $userId)
            ->where('house_id', $houseId)
            ->first();

        if ($temporaryBlock) {
            $temporaryBlock->delete();
            return true;
        }

        return false;
    }

    /**
     * Находит активную временную блокировку пользователя для дома
     */
    public function findUserTemporaryBlock(int $houseId, int $userId): ?TemporaryBlock
    {
        return TemporaryBlock::where('house_id', $houseId)
            ->where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->first();
    }
}

