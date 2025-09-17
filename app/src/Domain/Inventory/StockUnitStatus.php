<?php
// src/Domain/Inventory/StockUnitStatus.php
declare(strict_types=1);

namespace App\Domain\Inventory;

enum StockUnitStatus: string { case AVAILABLE='available'; case RESERVED='reserved'; case SOLD='sold'; case RETURNED='returned'; }
