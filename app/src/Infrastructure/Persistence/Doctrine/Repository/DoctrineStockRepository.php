<?php
// src/Infrastructure/Persistence/Doctrine/Repository/DoctrineStockRepository.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Inventory\{StockRepository, StockUnit, StockUnitId, GoodsReceiptId, StockUnitStatus};
use App\Domain\Catalog\ArticleId;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineStockUnit;
use App\Infrastructure\Persistence\Doctrine\Mapper\StockUnitMapper;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineStockRepository implements StockRepository
{
    public function __construct(private EntityManagerInterface $em, private StockUnitMapper $m) {}

    public function availableByArticle(ArticleId $id): array
    {
        $list = $this->em->getRepository(DoctrineStockUnit::class)->findBy(['article_id'=>(string)$id, 'status'=>StockUnitStatus::AVAILABLE->value]);
        return array_map(fn($e)=>$this->m->toDomain($e), $list);
    }

    public function byId(StockUnitId $id): ?StockUnit
    {
        $e = $this->em->find(DoctrineStockUnit::class, (string)$id);
        return $e ? $this->m->toDomain($e) : null;
    }

    public function save(StockUnit $unit): void
    {
        $e = $this->m->toEntity($unit);
        $this->em->persist($e);
        $this->em->flush();
    }

    public function saveAll(array $units): void
    {
        foreach ($units as $u) $this->em->persist($this->m->toEntity($u));
        $this->em->flush();
    }

    public function notSoldUnitsOfReceipt(GoodsReceiptId $id): array
    {
        $list = $this->em->getRepository(DoctrineStockUnit::class)->findBy([
            'origin_receipt_id'=>(string)$id,
        ]);
        $list = array_filter($list, fn($e)=>$e->status !== StockUnitStatus::SOLD->value);
        return array_map(fn($e)=>$this->m->toDomain($e), $list);
    }
}
