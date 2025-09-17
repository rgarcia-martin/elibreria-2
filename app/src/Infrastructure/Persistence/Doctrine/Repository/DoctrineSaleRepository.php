<?php
// src/Infrastructure/Persistence/Doctrine/Repository/DoctrineSaleRepository.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Sales\{SaleRepository, Sale, SaleId};
use App\Infrastructure\Persistence\Doctrine\Entity\{DoctrineSale, DoctrineSaleLine};
use App\Infrastructure\Persistence\Doctrine\Mapper\SaleMapper;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineSaleRepository implements SaleRepository
{
    public function __construct(private EntityManagerInterface $em, private SaleMapper $m) {}

    public function byId(SaleId $id): ?Sale
    {
        $sale = $this->em->find(DoctrineSale::class, (string)$id);
        if (!$sale) return null;
        $lines = $this->em->getRepository(DoctrineSaleLine::class)->findBy(['sale_id'=>(string)$id]);
        return $this->m->toDomain($sale, $lines);
    }

    public function save(Sale $s): void
    {
        $e = $this->m->toEntity($s);
        $this->em->persist($e);
        // upsert líneas (simplificado: borrar e insertar)
        $this->em->createQuery('DELETE FROM '.DoctrineSaleLine::class.' l WHERE l.sale_id = :sid')->setParameter('sid', (string)$s->id())->execute();
        foreach ($s->lines() as $l) {
            $this->em->persist($this->m->lineToEntity($l, (string)$s->id()));
        }
        $this->em->flush();
    }
}
