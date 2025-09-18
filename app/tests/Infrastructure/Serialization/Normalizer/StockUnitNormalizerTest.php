<?php
// tests/Infrastructure/Serialization/Normalizer/StockUnitNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Catalog\ArticleId;
use App\Domain\Common\{Money, Percentage, ProfitSharing};
use App\Domain\Inventory\{GoodsReceiptId, StockUnit, StockUnitId, StockUnitStatus};
use App\Domain\Locations\LocationId;
use App\Domain\Partners\ProviderId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class StockUnitNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const COST = 500;
    public const MERCHANT_80 = 80.0;
    public const PROVIDER_20 = 20.0;

    public function test_normalizes_stock_unit(): void
    {
        $serializer = $this->makeSerializer();

        $unit = new StockUnit(
            new StockUnitId(Uuid::uuid4()->toString()),
            new ArticleId(Uuid::uuid4()->toString()),
            new GoodsReceiptId(Uuid::uuid4()->toString()),
            new ProviderId(Uuid::uuid4()->toString()),
            new ProfitSharing(new Percentage(self::MERCHANT_80), new Percentage(self::PROVIDER_20)),
            new Money(self::COST, self::CURRENCY_EUR),
            new LocationId(Uuid::uuid4()->toString()),
            StockUnitStatus::AVAILABLE,
            null
        );

        $data = $serializer->normalize($unit);
        self::assertSame(self::COST, $data['unitCost']['amount']);
        self::assertSame(StockUnitStatus::AVAILABLE->value, $data['status']);
    }
}
