<?php
// tests/Domain/Common/MoneyTest.php
declare(strict_types=1);

namespace Tests\Domain\Common;

use App\Domain\Common\Money;
use LogicException;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public const CURRENCY_EUR = 'EUR';
    public const CURRENCY_USD = 'USD';

    public function test_addition_with_same_currency(): void
    {
        $a = new Money(1000, self::CURRENCY_EUR);
        $b = new Money(250, self::CURRENCY_EUR);

        $sum = $a->add($b);

        self::assertSame(1250, $sum->amount);
        self::assertSame(self::CURRENCY_EUR, $sum->currency);
    }

    public function test_subtraction_with_same_currency(): void
    {
        $a = new Money(1000, self::CURRENCY_EUR);
        $b = new Money(250, self::CURRENCY_EUR);

        $diff = $a->sub($b);

        self::assertSame(750, $diff->amount);
        self::assertSame(self::CURRENCY_EUR, $diff->currency);
    }

    public function test_multiplication(): void
    {
        $a = new Money(1000, self::CURRENCY_EUR);

        $result = $a->mul(1.5);

        self::assertSame(1500, $result->amount);
        self::assertSame(self::CURRENCY_EUR, $result->currency);
    }

    public function test_currency_mismatch_throws(): void
    {
        $this->expectException(LogicException::class);

        $a = new Money(1000, self::CURRENCY_EUR);
        $b = new Money(100, self::CURRENCY_USD);

        $a->add($b);
    }
}
