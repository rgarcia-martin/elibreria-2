<?php
// src/Domain/Common/Uuid.php
declare(strict_types=1);

namespace App\Domain\Common;

class Uuid
{
    public function __construct(public string $value)
    {
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $value)) {
            throw new \InvalidArgumentException('UUID inválido');
        }
    }
    public function __toString(): string { return $this->value; }
}
