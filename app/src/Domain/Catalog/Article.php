<?php
// src/Domain/Catalog/Article.php
declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Domain\Common\{Money, Barcode, Percentage};

final class Article
{
    public function __construct(
        private ArticleId $id,
        private string $name,
        private ?Barcode $barcode,
        private Money $basePrice,
        private Percentage $taxRate,
    ) {}

    public function id(): ArticleId { return $this->id; }
    public function name(): string { return $this->name; }
    public function barcode(): ?Barcode { return $this->barcode; }
    public function basePrice(): Money { return $this->basePrice; }
    public function taxRate(): Percentage { return $this->taxRate; }
}
