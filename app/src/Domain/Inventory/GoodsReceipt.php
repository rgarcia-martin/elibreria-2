<?php
// src/Domain/Inventory/GoodsReceipt.php
declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Domain\Partners\ProviderId;

final class GoodsReceipt
{
    /** @var GoodsReceiptLine[] */
    private array $lines = [];
    /** @var GoodsReceiptPhoto[] */
    private array $photos = [];

    public function __construct(
        private GoodsReceiptId $id,
        private GoodsReceiptType $type,
        private \DateTimeImmutable $receivedAt,
        private ?ProviderId $providerId,
        private ?\DateTimeImmutable $returnDueAt = null,
        private GoodsReceiptState $state = GoodsReceiptState::OPEN,
    ) {}

    public function id(): GoodsReceiptId { return $this->id; }
    public function type(): GoodsReceiptType { return $this->type; }
    public function providerId(): ?ProviderId { return $this->providerId; }
    public function receivedAt(): \DateTimeImmutable { return $this->receivedAt; }
    public function returnDueAt(): ?\DateTimeImmutable { return $this->returnDueAt; }

    public function addLine(GoodsReceiptLine $l): void { $this->lines[] = $l; }
    public function addPhoto(GoodsReceiptPhoto $p): void { $this->photos[] = $p; }

    /** @return GoodsReceiptLine[] */ public function lines(): array { return $this->lines; }
    /** @return GoodsReceiptPhoto[] */ public function photos(): array { return $this->photos; }

    public function close(): void { $this->state = GoodsReceiptState::CLOSED; }
}
