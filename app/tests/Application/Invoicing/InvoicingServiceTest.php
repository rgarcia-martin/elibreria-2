<?php
// tests/Application/Invoicing/InvoicingServiceTest.php
declare(strict_types=1);

namespace Tests\Application\Invoicing;

use App\Domain\Common\Money;
use App\Domain\Identity\{CompanyIdentityId, CompanyIdentityRepository};
use App\Domain\Invoicing\{Invoice, InvoiceFormat, InvoiceRepository, InvoiceType, InvoicingService};
use App\Domain\Ports\{DigitalInvoiceEmitterPort, InvoiceNumberSequencerPort};
use App\Domain\Sales\{Sale, SaleId, SaleRepository, SaleStatus};
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Tests\Support\BaseTestCase;

final class InvoicingServiceTest extends BaseTestCase
{
    public const SERIES           = 'A';
    public const GENERATED_NUMBER = 'A-000001';
    public const TOTAL_AMOUNT     = 1000;

    /** @var InvoiceRepository&MockObject */
    private $invoiceRepo;
    /** @var SaleRepository&MockObject */
    private $saleRepo;
    /** @var InvoiceNumberSequencerPort&MockObject */
    private $sequencer;
    /** @var DigitalInvoiceEmitterPort&MockObject */
    private $emitter;
    /** @var CompanyIdentityRepository&MockObject */
    private $identityRepo;

    private InvoicingService $service;

    protected function setUp(): void
{
    parent::setUp();

    $this->invoiceRepo = $this->createMock(InvoiceRepository::class);
    $this->saleRepo    = $this->createMock(SaleRepository::class);
    $this->sequencer   = $this->createMock(InvoiceNumberSequencerPort::class);
    $this->emitter     = $this->createMock(DigitalInvoiceEmitterPort::class);
    $this->identityRepo= $this->createMock(CompanyIdentityRepository::class);
    $this->clock       = $this->createMock(\App\Domain\Common\Clock::class);

    $this->service = new InvoicingService(
        $this->invoiceRepo,
        $this->saleRepo,
        $this->sequencer,
        $this->emitter,
        $this->identityRepo,
        $this->clock // <— 5º parámetro esperado
    );
}

    public function test_generate_invoice_for_sale(): void
    {
        $sale = $this->createConfiguredMock(Sale::class, [
            'id'                  => new SaleId(Uuid::uuid4()->toString()),
            'status'              => SaleStatus::CHARGED,
            'lines'               => [],
            'totalAfterDiscounts' => new Money(self::TOTAL_AMOUNT, self::CURRENCY_EUR),
        ]);

        $this->saleRepo->method('byId')->willReturn($sale);
        $this->identityRepo->method('byId')->willReturn(
            $this->createStub(\App\Domain\Identity\CompanyIdentity::class)
        );

        $this->sequencer->method('nextNumberFor')->with(self::SERIES)->willReturn(self::GENERATED_NUMBER);
        $this->emitter->method('emit')->with(self::isInstanceOf(Invoice::class))->willReturn('OK');

        $this->invoiceRepo->expects(self::once())->method('save')->with(self::isInstanceOf(\App\Domain\Invoicing\Invoice::class));

        $inv = $this->service->generateForSale(
            new CompanyIdentityId(Uuid::uuid4()->toString()),
            self::SERIES,
            InvoiceFormat::DIGITAL,
            InvoiceType::NORMAL,
            $sale->id()
        );

        self::assertSame(self::GENERATED_NUMBER, $inv->number());
    }
}
