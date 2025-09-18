<?php
// tests/Infrastructure/Serialization/Normalizer/LocationNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Locations\{Location, LocationId};
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class LocationNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const CODE = 'A1';
    public const NAME = 'Main Shelf';

    public function test_normalizes_location(): void
    {
        $serializer = $this->makeSerializer();

        $loc = $this->createMock(Location::class);
        $loc->method('id')->willReturn(new LocationId(Uuid::uuid4()->toString()));
        $loc->method('code')->willReturn(self::CODE);
        $loc->method('name')->willReturn(self::NAME);

        $data = $serializer->normalize($loc);
        self::assertSame(self::CODE, $data['code']);
        self::assertSame(self::NAME, $data['name']);
    }
}
