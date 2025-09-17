<?php
// src/Domain/Common/Barcode.php
declare(strict_types=1);

namespace App\Domain\Common;

final readonly class Barcode
{
    public function __construct(
        public string $value
    ) {}
}
