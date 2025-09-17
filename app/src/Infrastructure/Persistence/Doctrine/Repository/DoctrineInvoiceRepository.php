<?php
// src/Infrastructure/Persistence/Doctrine/Repository/DoctrineInvoiceRepository.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Invoicing\{InvoiceRepository, Invoice, InvoiceId};
use App\Infrastructure\Persistence\Doctrine\Entity\{DoctrineInvoice, DoctrineInvoiceLine};
use App\Infrastructure\Persistence\Doctrine\Mapper\InvoiceMapper;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineInvoiceRepository implements InvoiceRepository
{
    public function __construct(private EntityManagerInterface $em, private InvoiceMapper $m) {}

    public function byId(InvoiceId $id): ?Invoice
    {
        $e = $this->em->find(DoctrineInvoice::class, (string)$id);
        if (!$e) return null;
        $lines = $this->em->getRepository(DoctrineInvoiceLine::class)->findBy(['invoice_id'=>$e->id]);
        return $this->m->toDomain($e, $lines);
    }

    public function save(Invoice $i): void
    {
        $e = $this->m->toEntity($i);
        $this->em->persist($e);
        $this->em->createQuery('DELETE FROM '.DoctrineInvoiceLine::class.' l WHERE l.invoice_id = :id')
            ->setParameter('id', (string)$i->id())->execute();
        foreach ($i->lines() as $l) {
            $this->em->persist($this->m->lineToEntity($l, (string)$i->id()));
        }
        $this->em->flush();
    }
}
