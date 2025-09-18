<?php
// src/Domain/Sales/Payment.php
declare(strict_types=1);

namespace App\Domain\Sales;

use App\Domain\Common\Money;

readonly class Payment
{
    public function __construct(public Money $amount, public PaymentMethod $method) {}
}
