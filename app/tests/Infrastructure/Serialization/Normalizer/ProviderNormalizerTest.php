<?php
// tests/Infrastructure/Serialization/Normalizer/ProviderNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Partners\{Provider, ProviderId};
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class ProviderNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const NAME  = 'Acme Supplies';
    public const EMAIL = 'billing@example.com';
    public const PHONE = '+34-600-000-000';

    public function test_normalizes_provider_with_optional_fields(): void
    {
        $serializer = $this->makeSerializer();

        $provider = $this->createMock(Provider::class);
        $provider->method('id')->willReturn(new ProviderId(Uuid::uuid4()->toString()));
        $provider->method('name')->willReturn(self::NAME);
        $provider->method('email')->willReturn(self::EMAIL);
        $provider->method('phone')->willReturn(self::PHONE);

        $data = $serializer->normalize($provider);
        self::assertSame(self::NAME, $data['name']);
        self::assertSame(self::EMAIL, $data['email']);
        self::assertSame(self::PHONE, $data['phone']);
    }
}
