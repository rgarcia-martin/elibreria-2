<?php
// tests/Infrastructure/Serialization/Normalizer/InvoiceLineNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Common\{Money, Percentage};
use App\Domain\Invoicing\InvoiceLine;
use PHPUnit\Framework\TestCase;

final class InvoiceLineNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const DESC = 'Line';
    public const QTY = 3;
    public const PRICE = 700;

    public function test_normalizes_invoice_line(): void
    {
        $serializer = $this->makeSerializer();

        $line = new InvoiceLine(
            self::DESC,
            self::QTY,
            new Money(self::PRICE, self::CURRENCY_EUR),
            new Percentage(self::TAX_21)
        );

        $data = $serializer->normalize($line);
        self::assertSame(self::DESC, $data['description']);
        self::assertSame(self::QTY, $data['quantity']);
        self::assertSame(self::PRICE, $data['unitPrice']['amount']);
    }
}
