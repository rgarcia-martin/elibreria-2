<?php
// src/Domain/Common/SystemClock.php
declare(strict_types=1);

namespace App\Domain\Common;

final class SystemClock implements Clock
{
    public function now(): \DateTimeImmutable { return new \DateTimeImmutable('now'); }
}
