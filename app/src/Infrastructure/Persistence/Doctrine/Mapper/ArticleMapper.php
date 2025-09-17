<?php
// src/Infrastructure/Persistence/Doctrine/Mapper/ArticleMapper.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Catalog\{Article, ArticleId};
use App\Domain\Common\{Money, Barcode, Percentage};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineArticle;

final class ArticleMapper
{
    public function toEntity(Article $a): DoctrineArticle
    {
        $e = new DoctrineArticle();
        $e->id = (string)$a->id();
        $e->name = $a->name();
        $e->barcode = $a->barcode()?->value;
        $e->base_price_amount = $a->basePrice()->amount;
        $e->base_price_currency = $a->basePrice()->currency;
        $e->tax_rate = $a->taxRate()->value;
        return $e;
    }

    public function toDomain(DoctrineArticle $e): Article
    {
        return new Article(
            new ArticleId($e->id),
            $e->name,
            $e->barcode ? new Barcode($e->barcode) : null,
            new Money($e->base_price_amount, $e->base_price_currency),
            new Percentage($e->tax_rate),
        );
    }
}
