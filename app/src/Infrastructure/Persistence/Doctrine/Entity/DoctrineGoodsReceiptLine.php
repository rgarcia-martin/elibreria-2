<?php
// src/Infrastructure/Persistence/Doctrine/Entity/DoctrineGoodsReceiptLine.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'goods_receipt_lines')]
class DoctrineGoodsReceiptLine
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\ManyToOne(targetEntity: DoctrineGoodsReceipt::class, inversedBy: 'lines')]
    #[ORM\JoinColumn(name: 'receipt_id', referencedColumnName: 'id', nullable: false)]
    public DoctrineGoodsReceipt $receipt;

    #[ORM\Column(type: 'string', length: 36)]
    public string $article_id;

    #[ORM\Column(type: 'integer')]
    public int $quantity;

    #[ORM\Column(type: 'integer')]
    public int $unit_cost_amount;

    #[ORM\Column(type: 'string', length: 3)]
    public string $unit_cost_currency = 'EUR';

    #[ORM\Column(type: 'float')]
    public float $merchant_share;

    #[ORM\Column(type: 'float')]
    public float $provider_share;

    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    public ?string $initial_location_id = null;
}
