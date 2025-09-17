<?php
// src/Infrastructure/Persistence/Doctrine/Entity/DoctrineGoodsReceiptPhoto.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'goods_receipt_photos')]
class DoctrineGoodsReceiptPhoto
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    public string $id;

    #[ORM\ManyToOne(targetEntity: DoctrineGoodsReceipt::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(name: 'receipt_id', referencedColumnName: 'id', nullable: false)]
    public DoctrineGoodsReceipt $receipt;

    #[ORM\Column(type: 'string', length: 1024)]
    public string $uri;

    #[ORM\Column(type: 'string', length: 64)]
    public string $mime;
}
