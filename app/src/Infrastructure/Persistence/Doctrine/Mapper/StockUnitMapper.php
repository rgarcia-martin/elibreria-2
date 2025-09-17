<?php
// src/Infrastructure/Persistence/Doctrine/Mapper/StockUnitMapper.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Inventory\{
    StockUnit, StockUnitId, StockUnitStatus, GoodsReceiptId
};
use App\Domain\Catalog\ArticleId;
use App\Domain\Partners\ProviderId;
use App\Domain\Common\{Money, ProfitSharing, Percentage};
use App\Domain\Locations\LocationId;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineStockUnit;

final class StockUnitMapper
{
    public function toEntity(StockUnit $u): DoctrineStockUnit
    {
        $e = new DoctrineStockUnit();
        $e->id = (string)$u->id();
        $e->article_id = (string)$u->articleId();
        $e->origin_receipt_id = (string)$u->originReceiptId();
        $e->provider_id = $u->providerId()?->value;
        $e->merchant_share = $u->profitSharing()->merchantShare->value;
        $e->provider_share = $u->profitSharing()->providerShare->value;
        $e->unit_cost_amount = $u->unitCost()->amount;
        $e->unit_cost_currency = $u->unitCost()->currency;
        $e->location_id = (string)$u->locationId();
        $e->status = $u->status()->value;
        return $e;
    }

    public function toDomain(DoctrineStockUnit $e): StockUnit
    {
        return new StockUnit(
            new StockUnitId($e->id),
            new ArticleId($e->article_id),
            new GoodsReceiptId($e->origin_receipt_id),
            $e->provider_id ? new ProviderId($e->provider_id) : null,
            new ProfitSharing(new Percentage($e->merchant_share), new Percentage($e->provider_share)),
            new Money($e->unit_cost_amount, $e->unit_cost_currency),
            new LocationId($e->location_id),
            StockUnitStatus::from($e->status),
            $e->sold_at
        );
    }
}
