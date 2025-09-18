<?php
// tests/Infrastructure/Serialization/Normalizer/GoodsReceiptAndLineNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Catalog\ArticleId;
use App\Domain\Common\{Clock, Money, Percentage, ProfitSharing};
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
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class GoodsReceiptAndLineNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const MERCHANT_80 = 80.0;
    public const PROVIDER_20 = 20.0;
    public const UNIT_COST   = 500;

    /** @var GoodsReceiptRepository&MockObject */
    private $receiptRepo;
    /** @var StockRepository&MockObject */
    private $stockRepo;
    /** @var ConsignmentReturnRepository&MockObject */
    private $returnsRepo;
    /** @var Clock&MockObject */
    private $clock;

    protected function setUp(): void
    {
        $this->receiptRepo = $this->createMock(GoodsReceiptRepository::class);
        $this->stockRepo   = $this->createMock(StockRepository::class);
        $this->returnsRepo = $this->createMock(ConsignmentReturnRepository::class);
        $this->clock       = $this->createMock(Clock::class);
    }

    public function test_normalizes_goods_receipt_and_line(): void
    {
        $serializer = $this->makeSerializer();

        $service = new GoodsReceiptService(
            $this->receiptRepo,
            $this->stockRepo,
            $this->returnsRepo,
            $this->clock
        );

        $providerId = new ProviderId(Uuid::uuid4()->toString());
        $articleId  = new ArticleId(Uuid::uuid4()->toString());
        $locationId = new LocationId(Uuid::uuid4()->toString());

        $this->receiptRepo->expects(self::once())->method('save')->with(self::isInstanceOf(GoodsReceipt::class));

        $receipt = $service->registerStructured(
            $providerId,
            GoodsReceiptType::CONSIGNMENT,
            (new \DateTimeImmutable())->modify('+30 days'),
            [[
                'articleId'     => $articleId,
                'qty'           => 2,
                'unitCost'      => new Money(self::UNIT_COST, self::CURRENCY_EUR),
                'profitSharing' => new ProfitSharing(new Percentage(self::MERCHANT_80), new Percentage(self::PROVIDER_20)),
                'locationId'    => $locationId,
            ]]
        );

        $dataReceipt = $serializer->normalize($receipt);
        self::assertSame(GoodsReceiptType::CONSIGNMENT->value, $dataReceipt['type']);
        self::assertCount(1, $dataReceipt['lines']);

        $line = $receipt->lines()[0];
        $dataLine = $serializer->normalize($line);
        self::assertSame(self::UNIT_COST, $dataLine['unitCost']['amount']);
    }
}
