<?php
// tests/Infrastructure/Serialization/Normalizer/PercentageNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Common\Percentage;
use PHPUnit\Framework\TestCase;

final class PercentageNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public function test_normalizes_percentage(): void
    {
        $serializer = $this->makeSerializer();
        $p = new Percentage(self::TAX_21);

        $data = $serializer->normalize($p);
        self::assertIsFloat($data);
        self::assertSame(self::TAX_21, $data);
    }
}
