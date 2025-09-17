<?php
// src/Domain/Sales/SaleLine.php
declare(strict_types=1);

namespace App\Domain\Sales;

use App\Domain\Common\{Money, Discount, Percentage, Quantity};
use App\Domain\Catalog\ArticleId;

final class SaleLine
{
    public function __construct(
        private SaleLineId $id,
        private ArticleId $articleId,
        private Quantity $quantity,
        private Money $unitPrice,
        private Discount $discount,
        private Percentage $taxRate
    ) {}

    public function id(): SaleLineId { return $this->id; }
    public function articleId(): ArticleId { return $this->articleId; }
    public function quantity(): Quantity { return $this->quantity; }
    public function taxRate(): Percentage { return $this->taxRate; }

    public function totalBeforeDiscount(): Money { return $this->unitPrice->mul($this->quantity->units); }
    public function totalAfterDiscount(): Money { return $this->discount->apply($this->totalBeforeDiscount()); }
}
