<?php
// tests/Infrastructure/Serialization/Normalizer/MoneyNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Common\Money;
use App\Infrastructure\Serialization\Normalizer\MoneyNormalizer;
use PHPUnit\Framework\TestCase;

final class MoneyNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const AMOUNT = 1234;

    public function test_normalizes_money(): void
    {
        $serializer = $this->makeSerializer();
        $money = new Money(self::AMOUNT, self::CURRENCY_EUR);

        $data = $serializer->normalize($money);
        self::assertIsArray($data);
        self::assertSame(self::AMOUNT, $data[MoneyNormalizer::KEY_AMOUNT]);
        self::assertSame(self::CURRENCY_EUR, $data[MoneyNormalizer::KEY_CURRENCY]);
    }
}
