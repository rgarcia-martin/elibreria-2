<?php
// src/Domain/Common/ProfitSharing.php
declare(strict_types=1);

namespace App\Domain\Common;

final readonly class ProfitSharing
{
    public function __construct(public Percentage $merchantShare, public Percentage $providerShare)
    {
        if (abs($merchantShare->value + $providerShare->value - 100) > 0.001) {
            throw new \InvalidArgumentException('Reparto debe sumar 100%');
        }
    }
    public static function selfOwned(): self { return new self(new Percentage(100), new Percentage(0)); }
}
