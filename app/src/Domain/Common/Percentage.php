<?php
// src/Domain/Common/Percentage.php
declare(strict_types=1);

namespace App\Domain\Common;

final readonly class Percentage
{
    public function __construct(public float $value)
    {
        if ($value < 0 || $value > 100) throw new \InvalidArgumentException('Porcentaje fuera de rango');
    }
    public function asRatio(): float { return $this->value/100.0; }
}
