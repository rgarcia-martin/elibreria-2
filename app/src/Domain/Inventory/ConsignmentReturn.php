<?php
// src/Domain/Inventory/ConsignmentReturn.php
declare(strict_types=1);

namespace App\Domain\Inventory;

class ConsignmentReturn
{
    /** @var StockUnitId[] */
    private array $units = [];

    public function __construct(
        private ConsignmentReturnId $id,
        private GoodsReceiptId $receiptId,
        private \DateTimeImmutable $createdAt,
        private ConsignmentReturnStatus $status = ConsignmentReturnStatus::DRAFT,
        private ?\DateTimeImmutable $confirmedAt = null
    ) {}

    public function id(): ConsignmentReturnId { return $this->id; }
    public function receiptId(): GoodsReceiptId { return $this->receiptId; }

    /** @param StockUnitId[] $unitIds */
    public function includeUnits(array $unitIds): void { $this->units = array_values($unitIds); }
    /** @return StockUnitId[] */
    public function units(): array { return $this->units; }

    public function markSent(): void { $this->status = ConsignmentReturnStatus::SENT; }
    public function confirm(\DateTimeImmutable $when): void
    {
        $this->status = ConsignmentReturnStatus::CONFIRMED;
        $this->confirmedAt = $when;
    }
}
