<?php
// tests/Domain/Common/DiscountTest.php
declare(strict_types=1);

namespace Tests\Domain\Common;

use App\Domain\Common\{Discount, Money, Percentage};
use PHPUnit\Framework\TestCase;

final class DiscountTest extends TestCase
{
    public const CURRENCY_EUR = 'EUR';
    public const TEN_PERCENT = 10.0;
    public const FIXED_200 = 200;

    public function test_percent_discount_is_applied(): void
    {
        $base = new Money(1000, self::CURRENCY_EUR);
        $discount = Discount::percent(new Percentage(self::TEN_PERCENT));

        $result = $discount->apply($base);

        self::assertSame(900, $result->amount);
        self::assertSame(self::CURRENCY_EUR, $result->currency);
    }

    public function test_fixed_discount_is_applied(): void
    {
        $base = new Money(1000, self::CURRENCY_EUR);
        $discount = Discount::fixed(new Money(self::FIXED_200, self::CURRENCY_EUR));

        $result = $discount->apply($base);

        self::assertSame(800, $result->amount);
        self::assertSame(self::CURRENCY_EUR, $result->currency);
    }

    public function test_none_discount_keeps_value(): void
    {
        $base = new Money(1000, self::CURRENCY_EUR);
        $discount = Discount::none();

        $result = $discount->apply($base);

        self::assertSame(1000, $result->amount);
        self::assertSame(self::CURRENCY_EUR, $result->currency);
    }
}
