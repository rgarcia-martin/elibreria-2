<?php
// tests/Domain/Sales/SaleTest.php
declare(strict_types=1);

namespace Tests\Domain\Sales;

use App\Domain\Catalog\ArticleId;
use App\Domain\Common\{Discount, Money, Percentage, Quantity};
use App\Domain\Sales\{Sale, SaleId, SaleLine, SaleLineId, SaleStatus};
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class SaleTest extends TestCase
{
    public const CURRENCY_EUR = 'EUR';
    public const TAX_21 = 21.0;

    public function test_total_before_and_after_discounts(): void
    {
        $sale = new Sale(
            new SaleId(Uuid::uuid4()->toString()),
            new \DateTimeImmutable(),
            SaleStatus::DRAFT,
            Discount::none(),
        );

        $line1 = new SaleLine(
            new SaleLineId(Uuid::uuid4()->toString()),
            new ArticleId(Uuid::uuid4()->toString()),
            new Quantity(2),
            new Money(500, self::CURRENCY_EUR), // unit
            Discount::none(),
            new Percentage(self::TAX_21)
        );

        $line2 = new SaleLine(
            new SaleLineId(Uuid::uuid4()->toString()),
            new ArticleId(Uuid::uuid4()->toString()),
            new Quantity(1),
            new Money(1000, self::CURRENCY_EUR),
            Discount::percent(new Percentage(10.0)),
            new Percentage(self::TAX_21)
        );

        $sale->addLine($line1);
        $sale->addLine($line2);

        // Before discounts: (2*500) + (1*1000) = 2000
        self::assertSame(2000, $sale->totalBeforeDiscounts()->amount);

        // After line discounts: line1=1000, line2=900 => 1900
        self::assertSame(1900, $sale->totalAfterDiscounts()->amount);
    }

    public function test_global_discount_is_applied_on_total(): void
    {
        $sale = new Sale(
            new SaleId(Uuid::uuid4()->toString()),
            new \DateTimeImmutable(),
            SaleStatus::DRAFT,
            Discount::percent(new Percentage(5.0))
        );

        $line = new SaleLine(
            new SaleLineId(Uuid::uuid4()->toString()),
            new ArticleId(Uuid::uuid4()->toString()),
            new Quantity(2),
            new Money(1000, self::CURRENCY_EUR),
            Discount::none(),
            new Percentage(self::TAX_21)
        );

        $sale->addLine($line);

        // Subtotal after line discounts = 2000 → minus 5% = 1900
        self::assertSame(1900, $sale->totalAfterDiscounts()->amount);
    }
}
