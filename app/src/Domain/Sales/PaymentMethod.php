<?php
// src/Domain/Sales/PaymentMethod.php
declare(strict_types=1);

namespace App\Domain\Sales;

enum PaymentMethod: string { case CASH='cash'; case CARD='card'; }
