<?php

namespace App\Services\OrderService;

use App\Models\House;
use App\Models\TemporaryBlock;
use App\Models\User;
use Carbon\Carbon;

class OrderValidationService
{
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

    public function checkDatesAvailability(House $house, array $datesToBlock, ?User $user = null): array
    {
        TemporaryBlock::cleanExpired();
        $calendar = $house->house_calendar;
        $bookedDates = $calendar ? ($calendar->dates ?? []) : [];
        
        $temporaryBlocks = TemporaryBlock::where('house_id', $house->house_id)
            ->where('expires_at', '>', now())
            ->get();
        
        $temporaryBlockedDates = [];
        foreach ($temporaryBlocks as $block) {
            $temporaryBlockedDates = array_merge($temporaryBlockedDates, $block->dates ?? []);
        }
        $temporaryBlockedDates = array_unique($temporaryBlockedDates);

        foreach ($datesToBlock as $date) {
            if (in_array($date, $bookedDates)) {
                return [
                    'available' => false,
                    'error' => "Дата {$date} уже занята",
                    'date' => $date,
                ];
            }
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

    public function createTemporaryBlock(House $house, User $user, array $dates, int $minutes = 10): TemporaryBlock
    {
        TemporaryBlock::where('house_id', $house->house_id)
            ->where('user_id', $user->user_id)
            ->delete();
        return TemporaryBlock::create([
            'house_id' => $house->house_id,
            'user_id' => $user->user_id,
            'dates' => $dates,
            'expires_at' => now()->addMinutes($minutes),
        ]);
    }

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

        $blockDates = $temporaryBlock->dates ?? [];
        sort($blockDates);
        sort($expectedDates);
        
        if ($blockDates !== $expectedDates) {
            $temporaryBlock->delete();
            return null;
        }

        return $temporaryBlock;
    }

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

    public function findUserTemporaryBlock(int $houseId, int $userId): ?TemporaryBlock
    {
        return TemporaryBlock::where('house_id', $houseId)
            ->where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->first();
    }
}

