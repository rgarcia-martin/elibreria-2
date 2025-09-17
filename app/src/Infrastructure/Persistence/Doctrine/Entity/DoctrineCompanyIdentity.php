<?php
// src/Infrastructure/Persistence/Doctrine/Entity/DoctrineCompanyIdentity.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'company_identities')]
class DoctrineCompanyIdentity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 255)]
    public string $legal_name;

    #[ORM\Column(type: 'string', length: 64)]
    public string $tax_id;

    #[ORM\Column(type: 'string', length: 255)]
    public string $address;

    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    public ?string $e_invoicing_id = null;
}
