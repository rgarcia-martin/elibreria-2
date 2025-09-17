<?php
// src/Domain/Sales/SaleStatus.php
declare(strict_types=1);

namespace App\Domain\Sales;

enum SaleStatus: string { case DRAFT='draft'; case CHARGED='charged'; }
