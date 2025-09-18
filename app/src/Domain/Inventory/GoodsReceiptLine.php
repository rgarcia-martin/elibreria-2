<?php
// src/Domain/Inventory/GoodsReceiptLine.php
declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Domain\Common\{Money, Quantity, ProfitSharing};
use App\Domain\Catalog\ArticleId;
use App\Domain\Locations\LocationId;

class GoodsReceiptLine
{
    public function __construct(
        private ArticleId $articleId,
        private Quantity $quantity,
        private Money $unitCost,
        private ProfitSharing $profitSharing,
        private ?LocationId $initialLocation = null
    ) {}
    public function articleId(): ArticleId { return $this->articleId; }
    public function quantity(): Quantity { return $this->quantity; }
    public function unitCost(): Money { return $this->unitCost; }
    public function profitSharing(): ProfitSharing { return $this->profitSharing; }
    public function initialLocation(): ?LocationId { return $this->initialLocation; }
}
