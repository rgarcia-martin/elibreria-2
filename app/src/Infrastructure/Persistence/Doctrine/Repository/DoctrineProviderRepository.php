<?php
// src/Infrastructure/Persistence/Doctrine/Repository/DoctrineProviderRepository.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Partners\{ProviderRepository, Provider, ProviderId};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineProvider;
use App\Infrastructure\Persistence\Doctrine\Mapper\ProviderMapper;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineProviderRepository implements ProviderRepository
{
    public function __construct(private EntityManagerInterface $em, private ProviderMapper $m) {}

    public function byId(ProviderId $id): ?Provider
    {
        $e = $this->em->find(DoctrineProvider::class, (string)$id);
        return $e ? $this->m->toDomain($e) : null;
    }

    public function save(Provider $p): void
    {
        $e = $this->m->toEntity($p);
        $this->em->persist($e);
        $this->em->flush();
    }
}
