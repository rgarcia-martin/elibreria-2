<?php
// src/Infrastructure/Persistence/Doctrine/Entity/DoctrineInvoiceLine.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoice_lines')]
class DoctrineInvoiceLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\Column(type: 'string', length: 36)]
    public string $invoice_id;

    #[ORM\Column(type: 'string', length: 255)]
    public string $description;

    #[ORM\Column(type: 'integer')]
    public int $units;

    #[ORM\Column(type: 'integer')]
    public int $unit_price_amount;

    #[ORM\Column(type: 'string', length: 3)]
    public string $unit_price_currency = 'EUR';

    #[ORM\Column(type: 'float')]
    public float $tax_rate;
}
