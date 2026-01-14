<?php

namespace App\Services;

class PaymentGatewaySimulator
{
    public function charge(array $payload): array
    {
        $tx = 'SIM-' . strtoupper(bin2hex(random_bytes(6)));

        

        $amount = (float) ($payload['amount'] ?? 0);
        $card   = (string) ($payload['card_number'] ?? '');
        $cvv    = (string) ($payload['cvv'] ?? '');
        
        if ($amount > 20000 && $payload['operation']=="topup") {
            return $this->decline($tx, 'INSUFFICIENT_FUNDS', 'Недостаточно средств на карте.');
        }

        if ($cvv === '000') {
            return $this->decline($tx, 'INVALID_CVV', 'Неверный CVV.');
        }

        if (str_ends_with($card, '1')) {
            return $this->decline($tx, 'DO_NOT_HONOR', 'Банк отклонил операцию. Попробуйте позже.');
        }

        if (random_int(1, 100) <= 10) {
            return $this->decline($tx, 'TEMPORARY_ERROR', 'Временная ошибка. Повторите попытку.');
        }

        return [
            'status' => 'approved',
            'transaction_id' => $tx,
            'reason_code' => null,
            'reason_message' => null,
        ];
    }

    private function decline(string $tx, string $code, string $message): array
    {
        return [
            'status' => 'declined',
            'transaction_id' => $tx,
            'reason_code' => $code,
            'reason_message' => $message,
        ];
    }
}
