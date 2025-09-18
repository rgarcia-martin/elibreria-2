<?php
// src/Domain/Inventory/StockUnit.php
declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Domain\Catalog\ArticleId;
use App\Domain\Partners\ProviderId;
use App\Domain\Common\{Money, ProfitSharing};
use App\Domain\Locations\LocationId;

class StockUnit
{
    public function __construct(
        private StockUnitId $id,
        private ArticleId $articleId,
        private GoodsReceiptId $originReceiptId,
        private ?ProviderId $providerId,
        private ProfitSharing $profitSharing,
        private Money $unitCost,
        private LocationId $locationId,
        private StockUnitStatus $status = StockUnitStatus::AVAILABLE,
        private ?\DateTimeImmutable $soldAt = null
    ) {}

    public function id(): StockUnitId { return $this->id; }
    public function articleId(): ArticleId { return $this->articleId; }
    public function originReceiptId(): GoodsReceiptId { return $this->originReceiptId; }
    public function providerId(): ?ProviderId { return $this->providerId; }
    public function status(): StockUnitStatus { return $this->status; }
    public function locationId(): LocationId { return $this->locationId; }
    public function profitSharing(): ProfitSharing { return $this->profitSharing; }
    public function unitCost(): Money { return $this->unitCost; }

    public function reserve(): void
    {
        if ($this->status !== StockUnitStatus::AVAILABLE) throw new \LogicException('No reservable');
        $this->status = StockUnitStatus::RESERVED;
    }
    public function sell(\DateTimeImmutable $when): void
    {
        if ($this->status === StockUnitStatus::SOLD) throw new \LogicException('Ya vendido');
        if ($this->status === StockUnitStatus::RETURNED) throw new \LogicException('Devuelto: no vendible');
        $this->status = StockUnitStatus::SOLD;
        $this->soldAt = $when;
    }
    public function markReturned(): void
    {
        if ($this->status === StockUnitStatus::SOLD) throw new \LogicException('Vendido: no retornable');
        $this->status = StockUnitStatus::RETURNED;
    }
    public function moveTo(LocationId $to): void { $this->locationId = $to; }
}
