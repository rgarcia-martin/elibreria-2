<?php
// tests/Infrastructure/Serialization/Normalizer/BarcodeNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Common\Barcode;
use PHPUnit\Framework\TestCase;

final class BarcodeNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const CODE = '0123456789012';

    public function test_normalizes_barcode(): void
    {
        $serializer = $this->makeSerializer();
        $b = new Barcode(self::CODE);

        $data = $serializer->normalize($b);
        self::assertSame(self::CODE, $data);
    }
}
