<?php
// src/Domain/Common/Barcode.php
// Patch: add guard to reject empty barcodes
declare(strict_types=1);

namespace App\Domain\Common;

class Barcode
{
    public const ERR_EMPTY = 'EMPTY_BARCODE';

    private string $value;

    public function __construct(string $value)
    {
        $value = \trim($value);
        if ($value === '') {
            throw new \InvalidArgumentException(self::ERR_EMPTY);
        }
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /** Optional accessor if you prefer usage without __toString */
    public function value(): string
    {
        return $this->value;
    }
}
