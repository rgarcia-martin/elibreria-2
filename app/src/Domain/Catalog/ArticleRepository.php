<?php
// src/Domain/Catalog/ArticleRepository.php
declare(strict_types=1);

namespace App\Domain\Catalog;

use App\Domain\Common\Barcode;

interface ArticleRepository
{
    public function byId(ArticleId $id): ?Article;
    public function byBarcode(Barcode $barcode): ?Article;
    public function save(Article $a): void;
}
