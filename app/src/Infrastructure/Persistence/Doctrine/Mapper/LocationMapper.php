<?php
// src/Infrastructure/Persistence/Doctrine/Mapper/LocationMapper.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Locations\{Location, LocationId};
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineLocation;

final class LocationMapper
{
    public function toEntity(Location $l): DoctrineLocation
    {
        $e = new DoctrineLocation();
        $e->id = (string)$l->id();
        $e->name = $l->name();
        $e->parent_id = $l->parent()?->value;
        return $e;
    }
    public function toDomain(DoctrineLocation $e): Location
    {
        return new Location(
            new LocationId($e->id),
            $e->name,
            $e->parent_id ? new LocationId($e->parent_id) : null
        );
    }
}
