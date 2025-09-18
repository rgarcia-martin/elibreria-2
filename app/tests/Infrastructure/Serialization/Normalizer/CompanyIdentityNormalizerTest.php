<?php
// tests/Infrastructure/Serialization/Normalizer/CompanyIdentityNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Identity\{CompanyIdentity, CompanyIdentityId};
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class CompanyIdentityNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const LEGAL = 'My Company SL';
    public const TAX   = 'B12345678';
    public const EMAIL = 'info@company.test';

    public function test_normalizes_company_identity(): void
    {
        $serializer = $this->makeSerializer();

        $ci = $this->createMock(CompanyIdentity::class);
        $ci->method('id')->willReturn(new CompanyIdentityId(Uuid::uuid4()->toString()));
        $ci->method('legalName')->willReturn(self::LEGAL);
        $ci->method('taxId')->willReturn(self::TAX);
        $ci->method('email')->willReturn(self::EMAIL);

        $data = $serializer->normalize($ci);
        self::assertSame(self::LEGAL, $data['legalName']);
        self::assertSame(self::TAX, $data['taxId']);
        self::assertSame(self::EMAIL, $data['email']);
    }
}
