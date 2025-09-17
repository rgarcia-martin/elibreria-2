<?php
// tests/Application/Sales/SalesServiceTest.php
declare(strict_types=1);

namespace Tests\Application\Sales;

use App\Domain\Catalog\{Article, ArticleId, ArticleRepository};
use App\Domain\Common\{Barcode, Clock, Discount, Money, Percentage, Quantity};
use App\Domain\Inventory\{GoodsReceiptId, StockRepository, StockUnit, StockUnitId, StockUnitStatus};
use App\Domain\Locations\LocationId;
use App\Domain\Partners\ProviderId;
use App\Domain\Ports\PaymentGatewayPort;
use App\Domain\Pricing\StockSelectionPolicy;
use App\Domain\Sales\{Sale, SaleId, SaleLine, SaleLineId, SaleRepository, SalesService, SaleStatus};
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Tests\Support\BaseTestCase;

final class SalesServiceTest extends BaseTestCase
{
    public const BARCODE      = '0123456789012';
    public const UNIT_PRICE   = 1000;
    public const MERCHANT_80  = 80.0;
    public const PROVIDER_20  = 20.0;
    public const COST_AMOUNT  = 500;

    private SalesService $service;

    /** @var SaleRepository&MockObject */
    private $saleRepo;
    /** @var ArticleRepository&MockObject */
    private $articleRepo;
    /** @var StockRepository&MockObject */
    private $stockRepo;
    /** @var PaymentGatewayPort&MockObject */
    private $payment;
    /** @var Clock&MockObject */
    private $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleRepo    = $this->createMock(SaleRepository::class);
        $this->articleRepo = $this->createMock(ArticleRepository::class);
        $this->stockRepo   = $this->createMock(StockRepository::class);
        $this->payment     = $this->createMock(PaymentGatewayPort::class);
        $this->clock       = $this->createMock(Clock::class);

        // Selector stub: do not hit repository internals
        $selector = new class implements StockSelectionPolicy {
            public function selectFor(\App\Domain\Sales\Sale $sale, StockRepository $stock): array
            {
                return []; // keep flow simple; selection is orthogonal to this test
            }
        };

        $this->service = new SalesService(
            $this->saleRepo,
            $this->articleRepo,
            $this->stockRepo,
            $selector,
            $this->payment,
            $this->clock
        );
    }

    public function test_start_add_apply_and_close(): void
    {
        $article = new Article(
            new ArticleId(Uuid::uuid4()->toString()),
            'Article',
            new Barcode(self::BARCODE),
            new Money(self::UNIT_PRICE, self::CURRENCY_EUR),
            new Percentage(self::TAX_21)
        );

        $this->articleRepo->method('byBarcode')->with(self::isInstanceOf(Barcode::class))->willReturn($article);

        $capturedSale = null;
        $this->saleRepo->method('save')->willReturnCallback(function (Sale $s) use (&$capturedSale): void {
            $capturedSale = $s;
        });
        $this->saleRepo->method('byId')->willReturnCallback(function (SaleId $id) use (&$capturedSale): ?Sale {
            return $capturedSale && (string)$capturedSale->id() === (string)$id ? $capturedSale : null;
        });

        $sale = $this->service->startSale();
        $this->service->addLineByBarcode(
            $sale->id(),
            new Barcode(self::BARCODE),
            1,
            new Money(self::UNIT_PRICE, self::CURRENCY_EUR),
            Discount::none()
        );

        // This keeps the flow consistent even if no stock assignment happens.
        $this->service->applyBestStock($sale->id());

        // Pay total before closing to avoid "insufficient payment"
        $total = $sale->totalAfterDiscounts();
        $this->service->payWithCash($sale->id(), new Money($total->amount, $total->currency));

        $this->service->close($sale->id());
        self::assertSame(SaleStatus::CHARGED, $sale->status());
    }
}
