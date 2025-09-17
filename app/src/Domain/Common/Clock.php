<?php
// src/Domain/Common/Clock.php
declare(strict_types=1);

namespace App\Domain\Common;

interface Clock { public function now(): \DateTimeImmutable; }
