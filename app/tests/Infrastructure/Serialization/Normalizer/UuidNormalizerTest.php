<?php
// tests/Infrastructure/Serialization/Normalizer/UuidNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Common\Uuid;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid as RamseyUuid;

final class UuidNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public function test_normalizes_uuid_to_string(): void
    {
        $serializer = $this->makeSerializer();
        $value = RamseyUuid::uuid4()->toString();
        $uuid = new Uuid($value);

        $data = $serializer->normalize($uuid);
        self::assertIsString($data);
        self::assertSame($value, $data);
    }
}
