<?php
// src/Domain/Common/Quantity.php
declare(strict_types=1);

namespace App\Domain\Common;

final readonly class Quantity
{
    public function __construct(public int $units)
    {
        if ($units <= 0) throw new \InvalidArgumentException('Cantidad debe ser > 0');
    }
}
