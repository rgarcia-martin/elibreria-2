<?php
// src/Domain/Locations/Location.php
declare(strict_types=1);

namespace App\Domain\Locations;

final class Location
{
    public function __construct(
        private LocationId $id,
        private string $name,
        private ?LocationId $parent = null
    ) {}
    public function id(): LocationId { return $this->id; }
    public function name(): string { return $this->name; }
    public function parent(): ?LocationId { return $this->parent; }
}
