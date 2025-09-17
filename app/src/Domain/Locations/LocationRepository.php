<?php
// src/Domain/Locations/LocationRepository.php
declare(strict_types=1);

namespace App\Domain\Locations;

interface LocationRepository
{
    public function byId(LocationId $id): ?Location;
    public function save(Location $l): void;
}
