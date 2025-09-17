<?php
// tests/Domain/Pricing/BestConditionsStockSelectorTest.php
declare(strict_types=1);

namespace Tests\Domain\Pricing;

use App\Domain\Catalog\ArticleId;
use App\Domain\Common\{Discount, Money, Percentage, ProfitSharing, Quantity};
use App\Domain\Inventory\{GoodsReceiptId, StockUnit, StockUnitId, StockUnitStatus};
use App\Domain\Locations\LocationId;
use App\Domain\Partners\ProviderId;
use App\Domain\Pricing\BestConditionsStockSelector;
use App\Domain\Sales\{Sale, SaleId, SaleLine, SaleLineId, SaleStatus};
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class BestConditionsStockSelectorTest extends TestCase
{
    public const CURRENCY_EUR = 'EUR';
    public const MERCHANT_80  = 80.0;
    public const PROVIDER_20  = 20.0;
    public const MERCHANT_20  = 20.0;
    public const PROVIDER_80  = 80.0;
    public const TAX_21       = 21.0;

    public function test_picks_units_with_best_merchant_share_first(): void
    {
        $policy = new BestConditionsStockSelector();

        $articleId = new ArticleId(Uuid::uuid4()->toString());

        $good = new StockUnit(
            new StockUnitId(Uuid::uuid4()->toString()),
            $articleId,
            new GoodsReceiptId(Uuid::uuid4()->toString()),
            new ProviderId(Uuid::uuid4()->toString()),
            new ProfitSharing(new Percentage(self::MERCHANT_80), new Percentage(self::PROVIDER_20)),
            new Money(500, self::CURRENCY_EUR),
            new LocationId(Uuid::uuid4()->toString()),
            StockUnitStatus::AVAILABLE,
            null
        );

        $bad = new StockUnit(
            new StockUnitId(Uuid::uuid4()->toString()),
            $articleId,
            new GoodsReceiptId(Uuid::uuid4()->toString()),
            new ProviderId(Uuid::uuid4()->toString()),
            new ProfitSharing(new Percentage(self::MERCHANT_20), new Percentage(self::PROVIDER_80)),
            new Money(500, self::CURRENCY_EUR),
            new LocationId(Uuid::uuid4()->toString()),
            StockUnitStatus::AVAILABLE,
            null
        );

        $sale = new Sale(new SaleId(Uuid::uuid4()->toString()), new \DateTimeImmutable(), SaleStatus::DRAFT, Discount::none());
        $line = new SaleLine(
            new SaleLineId(Uuid::uuid4()->toString()),
            $articleId,
            new Quantity(1),
            new Money(1000, self::CURRENCY_EUR),
            Discount::none(),
            new Percentage(self::TAX_21)
        );
        $sale->addLine($line);

        $picked = $policy->selectFor($sale, [$bad, $good]);

        self::assertCount(1, $picked);
        self::assertSame((string)$good->id(), (string)$picked[0]->id());
    }
}
