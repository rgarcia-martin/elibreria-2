<?php
// src/Infrastructure/Symfony/Controller/InvoicesController.php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controller;

use App\Domain\Invoicing\{InvoicingService, InvoiceRepository, InvoiceFormat, InvoiceType};
use App\Domain\Sales\SaleId;
use App\Domain\Identity\CompanyIdentityId;
use App\Infrastructure\Persistence\Doctrine\Entity\{DoctrineInvoice, DoctrineInvoiceLine};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};

#[Route('/api/invoices')]
final class InvoicesController extends BaseApiController
{
    public function __construct(
        private InvoicingService $svc,
        private InvoiceRepository $repo,
        private EntityManagerInterface $em
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(Request $r): JsonResponse
    {
        $p = $this->pagination($r);
        $qb = $this->em->createQueryBuilder()->select('i')->from(DoctrineInvoice::class,'i')
            ->setFirstResult($p['offset'])->setMaxResults($p['limit']);
        if ($sale = $r->query->get('sale_id')) $qb->andWhere('i.sale_id = :s')->setParameter('s', $sale);
        $rows = array_map(fn($i)=>[
            'id'=>$i->id,'number'=>$i->number,'issued_at'=>$i->issued_at->format(DATE_ATOM),
            'type'=>$i->type,'format'=>$i->format,'sale_id'=>$i->sale_id,'rectifies_id'=>$i->rectifies_id
        ], $qb->getQuery()->getResult());
        return $this->jsonOk(['items'=>$rows,'limit'=>$p['limit'],'offset'=>$p['offset']]);
    }

    // Caso de uso: generar factura desde venta
    #[Route('', methods: ['POST'])]
    public function generate(Request $r): JsonResponse
    {
        $d = $this->body($r);
        $inv = $this->svc->generateForSale(
            new CompanyIdentityId((string)$d['issuer_identity_id']),
            (string)$d['series_key'],
            InvoiceFormat::from((string)$d['format']),
            InvoiceType::from((string)$d['type']),
            new SaleId((string)$d['sale_id'])
        );
        return $this->jsonOk(['id'=>(string)$inv->id(),'number'=>$inv->number()], 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $i = $this->em->find(DoctrineInvoice::class, $id);
        if (!$i) return $this->jsonError('Not found',404);
        $lines = $this->em->getRepository(DoctrineInvoiceLine::class)->findBy(['invoice_id'=>$id]);
        return $this->jsonOk([
            'id'=>$i->id,'number'=>$i->number,'issued_at'=>$i->issued_at->format(DATE_ATOM),
            'type'=>$i->type,'format'=>$i->format,'sale_id'=>$i->sale_id,'rectifies_id'=>$i->rectifies_id,
            'lines'=>array_map(fn($l)=>[
                'id'=>$l->id,'description'=>$l->description,'units'=>$l->units,
                'unit_price_amount'=>$l->unit_price_amount,'currency'=>$l->unit_price_currency,'tax_rate'=>$l->tax_rate
            ], $lines)
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        return $this->jsonOk(['ok'=>$this->deleteById($this->em, DoctrineInvoice::class, $id)]);
    }
}
