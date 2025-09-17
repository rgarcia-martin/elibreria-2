<?php
// tests/Infrastructure/Persistence/Doctrine/Mapper/InvoiceMapperTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Common\{Money, Percentage};
use App\Domain\Identity\CompanyIdentityId;
use App\Domain\Invoicing\{Invoice, InvoiceFormat, InvoiceId, InvoiceLine, InvoiceType};
use App\Domain\Sales\SaleId;
use App\Infrastructure\Persistence\Doctrine\Entity\{DoctrineInvoice, DoctrineInvoiceLine};
use App\Infrastructure\Persistence\Doctrine\Mapper\InvoiceMapper;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class InvoiceMapperTest extends TestCase
{
    public const CURRENCY_EUR = 'EUR';
    public const NUMBER       = 'A-000001';
    public const DESC         = 'Test line';
    public const TAX_21       = 21.0;

    public function test_invoice_entity_mapping(): void
    {
        $mapper = new InvoiceMapper();

        $invoice = new Invoice(
            new InvoiceId(Uuid::uuid4()->toString()),
            self::NUMBER,
            new \DateTimeImmutable('now'),
            InvoiceType::NORMAL,
            InvoiceFormat::DIGITAL,
            new CompanyIdentityId(Uuid::uuid4()->toString()),
            new SaleId(Uuid::uuid4()->toString()),
            null
        );

        $invoice->addLine(new InvoiceLine(
            self::DESC,
            2,
            new Money(1000, self::CURRENCY_EUR),
            new Percentage(self::TAX_21)
        ));

        $entity = $mapper->toEntity($invoice);
        self::assertInstanceOf(DoctrineInvoice::class, $entity);
        self::assertSame(self::NUMBER, $entity->number);

        $lineEntity = $mapper->lineToEntity($invoice->lines()[0], (string)$invoice->id());
        self::assertInstanceOf(DoctrineInvoiceLine::class, $lineEntity);
        self::assertSame(self::DESC, $lineEntity->description);
    }
}
