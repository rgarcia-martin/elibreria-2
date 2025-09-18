<?php
// tests/Infrastructure/Serialization/Normalizer/SaleNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Common\{Discount, Money, Percentage, Quantity};
use App\Domain\Sales\{Sale, SaleId, SaleLine, SaleLineId, SaleStatus};
use App\Domain\Catalog\ArticleId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class SaleNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const UNIT_PRICE = 1500;

    public function test_normalizes_sale(): void
    {
        $serializer = $this->makeSerializer();

        $sale = new Sale(
            new SaleId(Uuid::uuid4()->toString()),
            new \DateTimeImmutable(),
            SaleStatus::DRAFT,
            Discount::none()
        );

        $line = new SaleLine(
            new SaleLineId(Uuid::uuid4()->toString()),
            new ArticleId(Uuid::uuid4()->toString()),
            new Quantity(1),
            new Money(self::UNIT_PRICE, self::CURRENCY_EUR),
            Discount::none(),
            new Percentage(self::TAX_21)
        );

        $sale->addLine($line);

        $data = $serializer->normalize($sale);
        self::assertSame(SaleStatus::DRAFT->value, $data['status']);
        self::assertSame(self::UNIT_PRICE, $data['total']['amount']);
    }
}
