<?php
// tests/Infrastructure/Serialization/Normalizer/UserNormalizerTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Serialization\Normalizer;

use App\Domain\Users\User;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class UserNormalizerTest extends TestCase
{
    use SerializerFactoryTrait;

    public const USERNAME = 'rafa';
    public const EMAIL    = 'rafa@example.com';
    public const ROLE     = 'ROLE_ADMIN';

    public function test_normalizes_user_without_sensitive_data(): void
    {
        $serializer = $this->makeSerializer();

        $user = $this->createMock(User::class);
        $user->method('id')->willReturn(new \App\Domain\Users\UserId(Uuid::uuid4()->toString()));
        $user->method('username')->willReturn(self::USERNAME);
        $user->method('email')->willReturn(self::EMAIL);
        $user->method('roles')->willReturn([self::ROLE]);

        $data = $serializer->normalize($user);
        self::assertSame(self::USERNAME, $data['username']);
        self::assertSame(self::EMAIL, $data['email']);
        self::assertSame([self::ROLE], $data['roles']);
        self::assertArrayNotHasKey('password', $data);
    }
}
