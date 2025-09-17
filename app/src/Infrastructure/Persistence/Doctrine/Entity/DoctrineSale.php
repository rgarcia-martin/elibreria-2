<?php
// src/Infrastructure/Persistence/Doctrine/Entity/DoctrineSale.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sales')]
class DoctrineSale
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $created_at;

    #[ORM\Column(type: 'string', length: 16)]
    public string $status = 'draft';

    #[ORM\Column(type: 'integer', nullable: true)]
    public ?int $global_discount_fixed_amount = null;

    #[ORM\Column(type: 'float', nullable: true)]
    public ?float $global_discount_percent = null;
}
