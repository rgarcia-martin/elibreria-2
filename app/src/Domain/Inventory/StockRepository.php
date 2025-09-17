<?php
// src/Domain/Inventory/StockRepository.php
declare(strict_types=1);

namespace App\Domain\Inventory;

use App\Domain\Catalog\ArticleId;

interface StockRepository
{
    /** @return StockUnit[] */
    public function availableByArticle(ArticleId $id): array;
    public function byId(StockUnitId $id): ?StockUnit;
    public function save(StockUnit $unit): void;
    /** @param StockUnit[] $units */
    public function saveAll(array $units): void;
    /** @return StockUnit[] */
    public function notSoldUnitsOfReceipt(GoodsReceiptId $id): array;
}
