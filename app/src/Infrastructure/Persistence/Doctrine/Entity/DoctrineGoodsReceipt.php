<?php
// src/Infrastructure/Persistence/Doctrine/Entity/DoctrineGoodsReceipt.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'goods_receipts')]
class DoctrineGoodsReceipt
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\Column(type: 'string', length: 16)]
    public string $type; // purchased|consignment

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $received_at;

    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    public ?string $provider_id = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $return_due_at = null;

    #[ORM\Column(type: 'string', length: 16)]
    public string $state = 'open';

    /** @var DoctrineGoodsReceiptLine[] */
    #[ORM\OneToMany(mappedBy: 'receipt', targetEntity: DoctrineGoodsReceiptLine::class, cascade: ['persist'], orphanRemoval: true)]
    public iterable $lines;

    /** @var DoctrineGoodsReceiptPhoto[] */
    #[ORM\OneToMany(mappedBy: 'receipt', targetEntity: DoctrineGoodsReceiptPhoto::class, cascade: ['persist'], orphanRemoval: true)]
    public iterable $photos;
}
