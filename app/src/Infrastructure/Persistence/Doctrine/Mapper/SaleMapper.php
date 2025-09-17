<?php
// src/Infrastructure/Persistence/Doctrine/Mapper/SaleMapper.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Sales\{Sale, SaleId, SaleLine, SaleLineId, SaleStatus};
use App\Domain\Catalog\ArticleId;
use App\Domain\Common\{Money, Discount, Percentage, Quantity};
use App\Infrastructure\Persistence\Doctrine\Entity\{DoctrineSale, DoctrineSaleLine};

final class SaleMapper
{
    public function toEntity(Sale $s): DoctrineSale
    {
        $e = new DoctrineSale();
        $e->id = (string)$s->id();
        $e->created_at = $s->createdAt();
        $e->status = $s->totalAfterDiscounts()->amount >= 0 ? strtolower((new \ReflectionEnum(SaleStatus::class))->getCase($s->totalAfterDiscounts() ? 'DRAFT' : 'DRAFT')->getName()) : 'draft';
        return $e;
    }

    public function lineToEntity(SaleLine $l, string $saleId): DoctrineSaleLine
    {
        $e = new DoctrineSaleLine();
        $e->id = (string)$l->id();
        $e->sale_id = $saleId;
        $e->article_id = (string)$l->articleId();
        $e->quantity = $l->quantity()->units;
        $e->unit_price_amount = $l->totalBeforeDiscount()->amount / max(1, $l->quantity()->units);
        $e->unit_price_currency = 'EUR';
        $e->discount_fixed_amount = null;
        $e->discount_percent = null;
        $e->tax_rate = $l->taxRate()->value;
        return $e;
    }

    public function toDomain(DoctrineSale $e, array $lines): Sale
    {
        $s = new Sale(new SaleId($e->id), $e->created_at, SaleStatus::from(strtoupper($e->status)));
        foreach ($lines as $el) {
            $s->addLine(new SaleLine(
                new SaleLineId($el->id),
                new ArticleId($el->article_id),
                new Quantity($el->quantity),
                new Money($el->unit_price_amount, $el->unit_price_currency),
                Discount::none(),
                new Percentage($el->tax_rate)
            ));
        }
        return $s;
    }
}
