<?php
// tests/Infrastructure/Serialization/Normalizer/InvoiceNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Common\{Money, Percentage};
use App\Domain\Identity\CompanyIdentityId;
use App\Domain\Invoicing\{Invoice, InvoiceFormat, InvoiceId, InvoiceLine, InvoiceType};
use App\Domain\Sales\SaleId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class InvoiceNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const NUMBER = 'A-000001';
    public const DESC = 'Item';
    public const QTY = 1;
    public const PRICE = 999;

    public function test_normalizes_invoice(): void
    {
        $serializer = $this->makeSerializer();

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
            self::QTY,
            new Money(self::PRICE, self::CURRENCY_EUR),
            new Percentage(self::TAX_21)
        ));

        $data = $serializer->normalize($invoice);
        self::assertSame(self::NUMBER, $data['number']);
        self::assertSame(InvoiceType::NORMAL->value, $data['type']);
        self::assertSame(InvoiceFormat::DIGITAL->value, $data['format']);
        self::assertSame(self::PRICE, $data['total']['amount']);
    }
}
