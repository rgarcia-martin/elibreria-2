<?php
// src/Domain/Pricing/StockSelectionPolicy.php
declare(strict_types=1);

namespace App\Domain\Pricing;

use App\Domain\Sales\Sale;
use App\Domain\Inventory\{StockRepository, StockUnitId};

interface StockSelectionPolicy
{
    /** @return array<string, StockUnitId[]> map[lineId-string => unitIds] */
    public function selectFor(Sale $sale, StockRepository $stock): array;
}
