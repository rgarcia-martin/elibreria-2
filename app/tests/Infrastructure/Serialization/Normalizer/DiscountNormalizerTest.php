<?php
// tests/Infrastructure/Serialization/Normalizer/DiscountNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Common\{Discount, Money, Percentage};
use App\Infrastructure\Serialization\Normalizer\DiscountNormalizer;
use PHPUnit\Framework\TestCase;

final class DiscountNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const FIXED_AMOUNT = 250;
    public const RATE_VALUE = 10.0;

    public function test_normalizes_none(): void
    {
        $serializer = $this->makeSerializer();
        $disc = Discount::none();

        $data = $serializer->normalize($disc);
        self::assertSame(DiscountNormalizer::TYPE_NONE, $data[DiscountNormalizer::KEY_TYPE]);
    }

    public function test_normalizes_fixed(): void
    {
        $serializer = $this->makeSerializer();
        $disc = Discount::fixed(new Money(self::FIXED_AMOUNT, self::CURRENCY_EUR));

        $data = $serializer->normalize($disc);
        self::assertSame(DiscountNormalizer::TYPE_FIXED, $data[DiscountNormalizer::KEY_TYPE]);
        self::assertSame(self::FIXED_AMOUNT, $data[DiscountNormalizer::KEY_VALUE]['amount']);
    }

    public function test_normalizes_percent(): void
    {
        $serializer = $this->makeSerializer();
        $disc = Discount::percent(new Percentage(self::RATE_VALUE));

        $data = $serializer->normalize($disc);
        self::assertSame(DiscountNormalizer::TYPE_RATE, $data[DiscountNormalizer::KEY_TYPE]);
        self::assertSame(self::RATE_VALUE, $data[DiscountNormalizer::KEY_VALUE]);
    }
}
