<?php
// src/Domain/Inventory/ConsignmentReturnRepository.php
declare(strict_types=1);

namespace App\Domain\Inventory;

interface ConsignmentReturnRepository
{
    public function byId(ConsignmentReturnId $id): ?ConsignmentReturn;
    public function save(ConsignmentReturn $ret): void;
    /** @return ConsignmentReturn[] */
    public function returnsOfReceipt(GoodsReceiptId $id): array;
}
