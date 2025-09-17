<?php
// src/Infrastructure/Persistence/Doctrine/Entity/DoctrineSaleLine.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sale_lines')]
class DoctrineSaleLine
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 36)]
    public string $sale_id;

    #[ORM\Column(type: 'string', length: 36)]
    public string $article_id;

    #[ORM\Column(type: 'integer')]
    public int $quantity;

    #[ORM\Column(type: 'integer')]
    public int $unit_price_amount;

    #[ORM\Column(type: 'string', length: 3)]
    public string $unit_price_currency = 'EUR';

    #[ORM\Column(type: 'integer', nullable: true)]
    public ?int $discount_fixed_amount = null;

    #[ORM\Column(type: 'float', nullable: true)]
    public ?float $discount_percent = null;

    #[ORM\Column(type: 'float')]
    public float $tax_rate;
}
