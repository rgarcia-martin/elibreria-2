<?php
// tests/Support/BaseTestCase.php
declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Common\Money;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    public const CURRENCY_EUR = 'EUR';
    public const CURRENCY_USD = 'USD';
    public const TAX_21 = 21.0;

    protected static function assertMoneyEquals(int $expectedAmount, string $expectedCurrency, Money $actual): void
    {
        self::assertSame($expectedAmount, $actual->amount, 'Unexpected amount');
        self::assertSame($expectedCurrency, $actual->currency, 'Unexpected currency');
    }
}
