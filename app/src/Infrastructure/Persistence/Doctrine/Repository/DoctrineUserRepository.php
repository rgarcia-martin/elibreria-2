<?php
// src/Infrastructure/Persistence/Doctrine/Repository/DoctrineUserRepository.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Users\{UserRepository, User, UserId};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineUser;
use App\Infrastructure\Persistence\Doctrine\Mapper\UserMapper;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserRepository implements UserRepository
{
    public function __construct(private EntityManagerInterface $em, private UserMapper $m) {}

    public function byId(UserId $id): ?User
    {
        $e = $this->em->find(DoctrineUser::class, (string)$id);
        return $e ? $this->m->toDomain($e) : null;
    }

    public function byEmail(string $email): ?User
    {
        $e = $this->em->getRepository(DoctrineUser::class)->findOneBy(['email'=>$email]);
        return $e ? $this->m->toDomain($e) : null;
    }

    public function save(User $u): void
    {
        $e = $this->m->toEntity($u);
        $this->em->persist($e);
        $this->em->flush();
    }
}
