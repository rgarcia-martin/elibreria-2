<?php
// tests/Application/Inventory/GoodsReceiptServiceTest.php
declare(strict_types=1);

namespace Tests\Application\Inventory;

use App\Domain\Catalog\ArticleId;
use App\Domain\Common\{Clock, Money, Percentage, ProfitSharing, Quantity};
use App\Domain\Inventory\{
    ConsignmentReturnRepository,
    GoodsReceipt,
    GoodsReceiptRepository,
    GoodsReceiptService,
    GoodsReceiptType,
    StockRepository
};
use App\Domain\Locations\LocationId;
use App\Domain\Partners\ProviderId;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Tests\Support\BaseTestCase;

final class GoodsReceiptServiceTest extends BaseTestCase
{
    public const MIME_JPEG   = 'image/jpeg';
    public const URI_A       = 'file:///tmp/a.jpg';
    public const MERCHANT_80 = 80.0;
    public const PROVIDER_20 = 20.0;
    public const UNIT_COST   = 500;
    public const QTY_TWO     = 2;
    public const DAYS_PLUS_30 = '+30 days';

    /** @var GoodsReceiptRepository&MockObject */
    private $receiptRepo;
    /** @var StockRepository&MockObject */
    private $stockRepo;
    /** @var ConsignmentReturnRepository&MockObject */
    private $returnsRepo;
    /** @var Clock&MockObject */
    private $clock;

    private GoodsReceiptService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->receiptRepo  = $this->createMock(GoodsReceiptRepository::class);
        $this->stockRepo    = $this->createMock(StockRepository::class);
        $this->returnsRepo  = $this->createMock(ConsignmentReturnRepository::class);
        $this->clock        = $this->createMock(Clock::class);

        $this->service = new GoodsReceiptService(
            $this->receiptRepo,
            $this->stockRepo,
            $this->returnsRepo,
            $this->clock
        );
    }

    public function test_register_structured_receipt(): void
    {
        $providerId  = new ProviderId(Uuid::uuid4()->toString());
        $articleId   = new ArticleId(Uuid::uuid4()->toString());
        $locationId  = new LocationId(Uuid::uuid4()->toString());
        $type        = GoodsReceiptType::CONSIGNMENT;
        $returnDueAt = (new \DateTimeImmutable('now'))->modify(self::DAYS_PLUS_30);

        $this->receiptRepo
            ->expects(self::once())
            ->method('save')
            ->with(self::isInstanceOf(GoodsReceipt::class));

        $receipt = $this->service->registerStructured(
            $providerId,
            $type,
            $returnDueAt,
            [[
                'articleId'     => $articleId,
                'qty'           => self::QTY_TWO,
                'unitCost'      => new Money(self::UNIT_COST, self::CURRENCY_EUR),
                'profitSharing' => new ProfitSharing(new Percentage(self::MERCHANT_80), new Percentage(self::PROVIDER_20)),
                'locationId'    => $locationId,
            ]]
        );

        self::assertSame($type, $receipt->type());
        self::assertCount(1, $receipt->lines());
    }

    public function test_register_by_photos(): void
    {
        $this->receiptRepo
            ->expects(self::once())
            ->method('save')
            ->with(self::isInstanceOf(GoodsReceipt::class));

        $receipt = $this->service->registerByPhotos(
            null,
            GoodsReceiptType::PURCHASED,
            null,
            [[ 'uri' => self::URI_A, 'mime' => self::MIME_JPEG ]]
        );

        self::assertSame(GoodsReceiptType::PURCHASED, $receipt->type());
        self::assertCount(1, $receipt->photos());
    }
}
