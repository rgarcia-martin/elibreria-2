<?php
// tests/Infrastructure/Persistence/Doctrine/Mapper/UserMapperTest.php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Users\{Role, User, UserId};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineUser;
use App\Infrastructure\Persistence\Doctrine\Mapper\UserMapper;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class UserMapperTest extends TestCase
{
    public const EMAIL = 'test@example.com';
    public const HASH = 'hash';

    public function test_user_mapping_roundtrip(): void
    {
        $mapper = new UserMapper();

        $domain = new User(
            new UserId(Uuid::uuid4()->toString()),
            self::EMAIL,
            self::HASH,
            [Role::ADMIN]
        );

        $entity = $mapper->toEntity($domain);

        self::assertInstanceOf(DoctrineUser::class, $entity);
        self::assertSame(self::EMAIL, $entity->email);
        self::assertContains(Role::ADMIN->value, $entity->roles);

        $back = $mapper->toDomain($entity);

        self::assertSame((string)$domain->id(), (string)$back->id());
        self::assertSame(self::EMAIL, $back->email());
        self::assertSame(self::HASH, (new \ReflectionClass($back))->getProperty('passwordHash')->getValue($back));
    }
}
