<?php
// src/Domain/Invoicing/InvoiceLine.php
declare(strict_types=1);

namespace App\Domain\Invoicing;

use App\Domain\Common\{Money, Percentage};

class InvoiceLine
{
    public function __construct(
        private string $description,
        private int $units,
        private Money $unitPrice,
        private Percentage $taxRate
    ) {}
    public function total(): Money { return $this->unitPrice->mul($this->units); }
}
