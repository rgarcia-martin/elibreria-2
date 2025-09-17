<?php
// src/Infrastructure/Adapters/Payment/InMemoryPaymentGateway.php
declare(strict_types=1);

namespace App\Infrastructure\Adapters\Payment;

use App\Domain\Ports\PaymentGatewayPort;
use App\Domain\Common\Money;

final class InMemoryPaymentGateway implements PaymentGatewayPort
{
    public function charge(Money $amount): string
    {
        // Simula operación aprobada y devuelve un ID
        return 'PAY-'.bin2hex(random_bytes(6));
    }
}
