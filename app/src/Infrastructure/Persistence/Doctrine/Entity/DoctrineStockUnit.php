<?php
// src/Infrastructure/Persistence/Doctrine/Entity/DoctrineStockUnit.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'stock_units')]
class DoctrineStockUnit
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 36)]
    public string $article_id;

    #[ORM\Column(type: 'string', length: 36)]
    public string $origin_receipt_id;

    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    public ?string $provider_id = null;

    #[ORM\Column(type: 'float')]
    public float $merchant_share;

    #[ORM\Column(type: 'float')]
    public float $provider_share;

    #[ORM\Column(type: 'integer')]
    public int $unit_cost_amount;

    #[ORM\Column(type: 'string', length: 3)]
    public string $unit_cost_currency = 'EUR';

    #[ORM\Column(type: 'string', length: 36)]
    public string $location_id;

    #[ORM\Column(type: 'string', length: 16)]
    public string $status = 'available';

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $sold_at = null;
}
