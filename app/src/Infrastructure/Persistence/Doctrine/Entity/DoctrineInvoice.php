<?php
// src/Infrastructure/Persistence/Doctrine/Entity/DoctrineInvoice.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoices')]
class DoctrineInvoice
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 64)]
    public string $number;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $issued_at;

    #[ORM\Column(type: 'string', length: 16)]
    public string $type; // simplified|normal|rectification

    #[ORM\Column(type: 'string', length: 16)]
    public string $format; // paper|digital

    #[ORM\Column(type: 'string', length: 36)]
    public string $issuer_identity_id;

    #[ORM\Column(type: 'string', length: 36)]
    public string $sale_id;

    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    public ?string $rectifies_id = null;
}
