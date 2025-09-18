<?php
// src/Domain/Pricing/BestConditionsStockSelector.php
declare(strict_types=1);

namespace App\Domain\Pricing;

use App\Domain\Sales\Sale;
use App\Domain\Inventory\{StockRepository, StockUnit, StockUnitId};

class BestConditionsStockSelector implements StockSelectionPolicy
{
    public function selectFor(Sale $sale, StockRepository $stock): array
    {
        $assignments = [];
        foreach ($sale->lines() as $line) {
            $available = $stock->availableByArticle($line->articleId());
            usort($available, function (StockUnit $a, StockUnit $b) {
                $r = $b->profitSharing()->merchantShare->value <=> $a->profitSharing()->merchantShare->value;
                return $r !== 0 ? $r : ($b->unitCost()->amount <=> $a->unitCost()->amount);
            });
            $needed = $line->quantity()->units;
            $picked = array_slice(array_map(fn(StockUnit $u)=>$u->id(), $available), 0, $needed);
            if (count($picked) !== $needed) throw new \LogicException('Stock insuficiente');
            $assignments[(string)$line->id()] = $picked;
        }
        return $assignments;
    }
}
