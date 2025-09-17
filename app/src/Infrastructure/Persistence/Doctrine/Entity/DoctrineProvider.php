<?php
// src/Infrastructure/Persistence/Doctrine/Entity/DoctrineProvider.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'providers')]
class DoctrineProvider
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 255)]
    public string $name;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    public ?string $tax_id = null;

    #[ORM\Column(type: 'string', length: 255)]
    public string $email;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    public ?string $phone = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    public ?string $address = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    public ?int $default_consignment_days = null;
}
