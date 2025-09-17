<?php
// src/Domain/Ports/PaymentGatewayPort.php
declare(strict_types=1);

namespace App\Domain\Ports;

use App\Domain\Common\Money;

interface PaymentGatewayPort
{
    public function charge(Money $amount): string;
}
