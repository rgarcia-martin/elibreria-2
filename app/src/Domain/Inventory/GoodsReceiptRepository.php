<?php
// src/Domain/Inventory/GoodsReceiptRepository.php
declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Domain\Partners\ProviderId;

interface GoodsReceiptRepository
{
    public function byId(GoodsReceiptId $id): ?GoodsReceipt;
    public function save(GoodsReceipt $r): void;

    /** @return GoodsReceipt[] */
    public function findByBucket(ConsignmentBucket $bucket, \DateTimeImmutable $now): array;

    public function countReceiptsForProvider(ProviderId $id): int;
    public function countPurchasedForProvider(ProviderId $id): int;
    public function countConsignmentsForProvider(ProviderId $id): int;
}
