<?php
// src/Infrastructure/Persistence/Doctrine/Mapper/UserMapper.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Users\{User, UserId, Role};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineUser;

final class UserMapper
{
    public function toEntity(User $u): DoctrineUser
    {
        $e = new DoctrineUser();
        $e->id = (string)$u->id();
        $e->email = $u->email();
        $e->password_hash = (new \ReflectionClass($u))->getProperty('passwordHash')->getValue($u);
        $e->roles = array_map(fn(Role $r)=>$r->value, $u->roles());
        return $e;
    }

    public function toDomain(DoctrineUser $e): User
    {
        $roles = array_map(fn(string $v)=>Role::from($v), $e->roles);
        return new User(new UserId($e->id), $e->email, $e->password_hash, $roles);
    }
}
