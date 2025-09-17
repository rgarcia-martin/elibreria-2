<?php
// src/Infrastructure/Persistence/Doctrine/Repository/DoctrineCompanyIdentityRepository.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Identity\{CompanyIdentityRepository, CompanyIdentity, CompanyIdentityId};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineCompanyIdentity;
use App\Infrastructure\Persistence\Doctrine\Mapper\CompanyIdentityMapper;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineCompanyIdentityRepository implements CompanyIdentityRepository
{
    public function __construct(private EntityManagerInterface $em, private CompanyIdentityMapper $m) {}

    public function byId(CompanyIdentityId $id): ?CompanyIdentity
    {
        $e = $this->em->find(DoctrineCompanyIdentity::class, (string)$id);
        return $e ? $this->m->toDomain($e) : null;
    }

    public function save(CompanyIdentity $c): void
    {
        $e = $this->m->toEntity($c);
        $this->em->persist($e);
        $this->em->flush();
    }
}
