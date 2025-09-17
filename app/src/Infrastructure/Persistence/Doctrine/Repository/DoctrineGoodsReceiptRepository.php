<?php
// src/Infrastructure/Persistence/Doctrine/Repository/DoctrineGoodsReceiptRepository.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Inventory\{
    GoodsReceiptRepository, GoodsReceipt, GoodsReceiptId, ConsignmentBucket, GoodsReceiptType
};
use App\Domain\Partners\ProviderId;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineGoodsReceipt;
use App\Infrastructure\Persistence\Doctrine\Mapper\GoodsReceiptMapper;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineGoodsReceiptRepository implements GoodsReceiptRepository
{
    public function __construct(private EntityManagerInterface $em, private GoodsReceiptMapper $m) {}

    public function byId(GoodsReceiptId $id): ?GoodsReceipt
    {
        $e = $this->em->find(DoctrineGoodsReceipt::class, (string)$id);
        return $e ? $this->m->toDomain($e) : null;
    }

    public function save(GoodsReceipt $r): void
    {
        $e = $this->m->toEntity($r);
        $this->em->persist($e);
        $this->em->flush();
    }

    public function findByBucket(ConsignmentBucket $bucket, \DateTimeImmutable $now): array
    {
        $qb = $this->em->createQueryBuilder()->select('r')->from(DoctrineGoodsReceipt::class, 'r');
        $expr = $qb->expr();
        if ($bucket === ConsignmentBucket::PURCHASED) {
            $qb->where('r.type = :t')->setParameter('t', GoodsReceiptType::PURCHASED->value);
        } elseif ($bucket === ConsignmentBucket::CONSIGNMENT_RETURNED) {
            $qb->where('r.state = :s')->setParameter('s', 'returned'); // simplificado si marcas retorno
        } elseif ($bucket === ConsignmentBucket::CONSIGNMENT_OVERDUE) {
            $qb->where('r.type = :t')->andWhere('r.return_due_at < :now')->setParameter('t','consignment')->setParameter('now',$now);
        } else {
            $qb->where('r.type = :t')->andWhere('r.return_due_at >= :now OR r.return_due_at IS NULL')->setParameter('t','consignment')->setParameter('now',$now);
        }
        return array_map(fn($e)=>$this->m->toDomain($e), $qb->getQuery()->getResult());
    }

    public function countReceiptsForProvider(ProviderId $id): int
    {
        return (int)$this->em->createQueryBuilder()
            ->select('COUNT(r.id)')->from(DoctrineGoodsReceipt::class,'r')
            ->where('r.provider_id = :p')->setParameter('p',(string)$id)
            ->getQuery()->getSingleScalarResult();
    }
    public function countPurchasedForProvider(ProviderId $id): int
    {
        return (int)$this->em->createQueryBuilder()
            ->select('COUNT(r.id)')->from(DoctrineGoodsReceipt::class,'r')
            ->where('r.provider_id = :p AND r.type = :t')->setParameter('p',(string)$id)->setParameter('t','purchased')
            ->getQuery()->getSingleScalarResult();
    }
    public function countConsignmentsForProvider(ProviderId $id): int
    {
        return (int)$this->em->createQueryBuilder()
            ->select('COUNT(r.id)')->from(DoctrineGoodsReceipt::class,'r')
            ->where('r.provider_id = :p AND r.type = :t')->setParameter('p',(string)$id)->setParameter('t','consignment')
            ->getQuery()->getSingleScalarResult();
    }
}
