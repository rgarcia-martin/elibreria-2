<?php
// src/Infrastructure/Persistence/Doctrine/Repository/DoctrineLocationRepository.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Locations\{LocationRepository, Location, LocationId};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineLocation;
use App\Infrastructure\Persistence\Doctrine\Mapper\LocationMapper;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineLocationRepository implements LocationRepository
{
    public function __construct(private EntityManagerInterface $em, private LocationMapper $m) {}

    public function byId(LocationId $id): ?Location
    {
        $e = $this->em->find(DoctrineLocation::class, (string)$id);
        return $e ? $this->m->toDomain($e) : null;
    }

    public function save(Location $l): void
    {
        $e = $this->m->toEntity($l);
        $this->em->persist($e);
        $this->em->flush();
    }
}
