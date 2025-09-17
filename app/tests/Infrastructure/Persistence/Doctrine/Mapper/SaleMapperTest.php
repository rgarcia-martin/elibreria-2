<?php
// tests/Infrastructure/Persistence/Doctrine/Mapper/SaleMapperTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Catalog\ArticleId;
use App\Domain\Common\{Discount, Money, Percentage, Quantity};
use App\Domain\Sales\{Sale, SaleId, SaleLine, SaleLineId, SaleStatus};
use App\Infrastructure\Persistence\Doctrine\Entity\{DoctrineSale, DoctrineSaleLine};
use App\Infrastructure\Persistence\Doctrine\Mapper\SaleMapper;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class SaleMapperTest extends TestCase
{
    public const CURRENCY_EUR = 'EUR';
    public const TAX_21       = 21.0;

    public function test_sale_entity_mapping(): void
    {
        $mapper = new SaleMapper();

        $sale = new Sale(
            new SaleId(Uuid::uuid4()->toString()),
            new \DateTimeImmutable(),
            SaleStatus::DRAFT,
            Discount::none()
        );

        $line = new SaleLine(
            new SaleLineId(Uuid::uuid4()->toString()),
            new ArticleId(Uuid::uuid4()->toString()),
            new Quantity(2),
            new Money(500, self::CURRENCY_EUR),
            Discount::none(),
            new Percentage(self::TAX_21)
        );

        $sale->addLine($line);

        $entity = $mapper->toEntity($sale);
        self::assertInstanceOf(DoctrineSale::class, $entity);

        $lineEntity = $mapper->lineToEntity($line, (string)$sale->id());
        self::assertInstanceOf(DoctrineSaleLine::class, $lineEntity);
        self::assertSame(2, $lineEntity->quantity);
    }
}
