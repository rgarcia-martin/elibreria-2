<?php
// src/Infrastructure/Adapters/Inventory/InMemoryConsignmentReturnRepository.php
declare(strict_types=1);

namespace App\Infrastructure\Adapters\Inventory;

use App\Domain\Inventory\{ConsignmentReturnRepository, ConsignmentReturn, ConsignmentReturnId, GoodsReceiptId};

final class InMemoryConsignmentReturnRepository implements ConsignmentReturnRepository
{
    /** @var array<string,ConsignmentReturn> */
    private array $store = [];

    public function byId(ConsignmentReturnId $id): ?ConsignmentReturn
    {
        return $this->store[(string)$id] ?? null;
    }

    public function save(ConsignmentReturn $ret): void
    {
        $this->store[(string)$ret->id()] = $ret;
    }

    public function returnsOfReceipt(GoodsReceiptId $id): array
    {
        return array_values(array_filter($this->store, fn(ConsignmentReturn $r) => (string)$r->receiptId() === (string)$id));
    }
}
