<?php
// src/Infrastructure/Persistence/Doctrine/Entity/DoctrineArticle.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'articles')]
class DoctrineArticle
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 255)]
    public string $name;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    public ?string $barcode = null;

    #[ORM\Column(type: 'integer')]
    public int $base_price_amount;

    #[ORM\Column(type: 'string', length: 3)]
    public string $base_price_currency = 'EUR';

    #[ORM\Column(type: 'float')]
    public float $tax_rate;
}
