<?php
// tests/Infrastructure/Serialization/Normalizer/SaleLineNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Catalog\ArticleId;
use App\Domain\Common\{Discount, Money, Percentage, Quantity};
use App\Domain\Sales\{SaleLine, SaleLineId};
use App\Infrastructure\Serialization\Normalizer\SaleLineNormalizer;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class SaleLineNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const UNIT_PRICE = 1000;
    public const QTY = 2;

    public function test_normalizes_sale_line(): void
    {
        $serializer = $this->makeSerializer();

        $line = new SaleLine(
            new SaleLineId(Uuid::uuid4()->toString()),
            new ArticleId(Uuid::uuid4()->toString()),
            new Quantity(self::QTY),
            new Money(self::UNIT_PRICE, self::CURRENCY_EUR),
            Discount::none(),
            new Percentage(self::TAX_21)
        );

        $data = $serializer->normalize($line);
        self::assertSame(self::QTY, $data[SaleLineNormalizer::KEY_QUANTITY]);
        self::assertSame(self::UNIT_PRICE, $data[SaleLineNormalizer::KEY_UNITPRICE]['amount']);
    }
}
